<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\Service;

use App\Service\HammerService;
use App\Service\OwnCloudService;
use Doctrine\ORM\EntityManagerInterface;
use GraphicServiceOrder\Entity\GsOrder;
use GraphicServiceOrder\Message\OwnCloudShareMessage;
use GraphicServiceOrder\Repository\GsOrderRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Service\UserManager;

class OrderService
{
    private $hammerService;
    private $ownCloudService;
    private $gsOrderRepository;
    private $appKernel;
    private $entityManager;
    private $messageBus;
    private $ownCloudFilesFolder;
    private $tokenStorage;

    /**
     * OrderService constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface                                                $entityManager
     * @param \App\Service\HammerService                                                          $hammerService
     * @param \App\Service\OwnCloudService                                                        $ownCloudService
     * @param \GraphicServiceOrder\Repository\GsOrderRepository                                   $gsOrderRepository
     * @param \Symfony\Component\HttpKernel\KernelInterface                                       $appKernel
     * @param \Symfony\Component\Messenger\MessageBusInterface                                    $messageBus
     * @param string                                                                              $ownCloudFilesFolder
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenStorage
     * @param \GraphicServiceOrder\Service\FileUploader                                           $fileUploader
     * @param \App\Service\UserManager                                                            $userManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        HammerService $hammerService,
        OwnCloudService $ownCloudService,
        GsOrderRepository $gsOrderRepository,
        KernelInterface $appKernel,
        MessageBusInterface $messageBus,
        string $ownCloudFilesFolder,
        TokenStorageInterface $tokenStorage,
        FileUploader $fileUploader,
        UserManager $userManager
    ) {
        $this->entityManager = $entityManager;
        $this->hammerService = $hammerService;
        $this->ownCloudService = $ownCloudService;
        $this->gsOrderRepository = $gsOrderRepository;
        $this->appKernel = $appKernel;
        $this->messageBus = $messageBus;
        $this->ownCloudFilesFolder = $ownCloudFilesFolder;
        $this->tokenStorage = $tokenStorage;
        $this->fileUploader = $fileUploader;
        $this->userManager = $userManager;
    }

    /**
     * Preset some values from user entity.
     *
     * @return \GraphicServiceOrder\Entity\GsOrder
     */
    public function prepareOrder()
    {
        $gsOrder = new GsOrder();
        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            $user = $token->getUser();
            $gsOrder
                ->setFullName($user->getFullName())
                ->setAddress($user->getAddress())
                ->setDepartment($user->getDepartment())
                ->setPostalcode($user->getPostalCode())
                ->setCity($user->getCity());
        }

        return $gsOrder;
    }

    /**
     * Update active user with submitted values.
     *
     * @param $gsOrder
     */
    private function updateUser($gsOrder)
    {
        $token = $this->tokenStorage->getToken();
        if (null !== $token) {
            /** @var \App\Entity\User $user */
            $user = $token->getUser();
            $user
                ->setFullName($gsOrder->getFullName())
                ->setDepartment($gsOrder->getDepartment())
                ->setAddress($gsOrder->getAddress())
                ->setPostalCode($gsOrder->getPostalcode())
                ->setCity($gsOrder->getCity());

            $this->userManager->updateUser($user);
        }
    }

    /**
     * Create a GsOrder.
     *
     * @param \GraphicServiceOrder\Entity\GsOrder $gsOrder
     * @param $uploadedFiles
     */
    public function createOrder(GsOrder $gsOrder, $form)
    {
        // Create a task on a jira project.
        $taskCreated = $this->createOrderTask($gsOrder);

        // Add task values to order entity.
        $gsOrder->setIssueId($taskCreated->id);
        $gsOrder->setIssueKey($taskCreated->key);

        // Store file locally.
        $gsOrder = $this->storeFile($gsOrder, $form);
        $gsOrder->setOrderStatus('new');

        $this->entityManager->persist($gsOrder);
        $this->entityManager->flush();

        // Notify messenger of new job.
        $this->messageBus->dispatch(new OwnCloudShareMessage($gsOrder->getId()));

        $this->updateUser($gsOrder);
        // @TODO: Send notification mail.
    }

    /**
     * Handle file transfer to OwnCloud.
     *
     * @param \GraphicServiceOrder\Entity\GsOrder $order
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handleOrderMessage(GsOrder $order)
    {
        $files = $order->getFiles();

        // If no files on order, consider all files received.
        if (empty($files)) {
            $order->setOrderStatus('received');
        } else {
            // Create a folder with issue key as name.
            $this->createFolder($order->getIssueKey());

            // Get all files on the order that have already been shared.
            $sharedFiles = $order->getOwnCloudSharedFiles();
            foreach ($files as $file) {
                // if a file exists on the entity that has not yet been shared.
                if (!\in_array($file, $sharedFiles)) {
                    // Attempt to share the file in owncloud.
                    $response = $this->shareFile($file, $order->getIssueKey());
                    $success = [201, 204];  // Successful responses;
                    // if file was shared successfully add to shared files array.
                    if (\in_array($response, $success)) {
                        $sharedFiles[] = $file;
                        $order->setOwnCloudSharedFiles($sharedFiles);
                    }
                }
            }
        }

        // If all files are considered shared change status to "received".
        $diff = array_diff($files, $order->getOwnCloudSharedFiles());
        if (empty($diff)) {
            $order->setOrderStatus('received');
            // Remove local files.
            foreach ($order->getOwnCloudSharedFiles() as $file) {
                // @TODO: Fix path parameters.
                $files_dir = $this->appKernel->getProjectDir().'/private/files/gs/';
                unlink($files_dir.$file);
            }
        }

        // Update entity.
        $this->entityManager->flush();
    }

    /**
     * Create a Jira task from a form submission.
     *
     * @param \GraphicServiceOrder\Entity\GsOrder $gsOrder
     *
     * @return mixed
     */
    private function createOrderTask(GsOrder $gsOrder)
    {
        $description = $this->getDescription($gsOrder);
        $data = [
            'fields' => [
                'project' => [
                    'id' => $_ENV['GS_ORDER_PROJECT_ID'],
                ],
                'summary' => $gsOrder->getJobTitle(),
                'description' => $description,
                'issuetype' => [
                    'id' => $_ENV['GS_ORDER_ISSUETYPE_ID'],
                ],
            ],
        ];
        $response = $this->hammerService->post('/rest/api/2/issue', $data);

        return $response;
    }

    /**
     * Create folder if none exists.
     *
     * @param $order_key
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function createFolder($order_key)
    {
        // @TODO: Fix path parameters.
        // Make sure folder does not already exist.
        $existing_folders = $this->ownCloudService->propFind('/owncloud/remote.php/dav/files/'.$_ENV['OWNCLOUD_USER_SHARED_DIR']);
        if (!\in_array($order_key.'/', $existing_folders)) {
            // Create folders
            $this->ownCloudService->mkCol('owncloud/remote.php/dav/files/'.$_ENV['OWNCLOUD_USER_SHARED_DIR'].$order_key);
            $this->ownCloudService->mkCol('owncloud/remote.php/dav/files/'.$_ENV['OWNCLOUD_USER_SHARED_DIR'].$order_key.'/_Materiale');
        }
    }

    /**
     * Share file in owncloud.
     *
     * @param $fileName
     * @param $order_id
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function shareFile($fileName, $order_id)
    {
        // @TODO: Fix path parameters.
        $ownCloudPath = $_ENV['OWNCLOUD_USER_SHARED_DIR'].$order_id.'/_Materiale/';
        $ocFilename = $order_id.'-'.$fileName;
        $file = file_get_contents($this->appKernel->getProjectDir().'/private/files/gs/'.$fileName);
        $response = $this->ownCloudService->sendFile(
            'owncloud/remote.php/dav/files/'.$ownCloudPath.$ocFilename,
            $file
        );

        return $response;
    }

    /**
     * Create description for task.
     *
     * @param $orderData
     *
     * @return string
     */
    private function getDescription(GsOrder $orderData)
    {
        // Create task description.
        $description = '*Opgavebeskrivelse* \\\\ ';
        foreach ($orderData->getOrderLines() as $order) {
            $description .= '- '.$order['amount'].' '.$order['type'].'\\\\ ';
        }
        $description .= $orderData->getDescription().'\\\\ ';
        $description .= ' \\\\ ';
        $description .= '[Åbn filer i OwnCloud|'.$this->ownCloudFilesFolder.'] \\\\ ';
        $description .= ' \\\\ ';

        // Create payment description.
        $description .= '*Hvem skal betale?* \\\\ ';
        if ($orderData->getMarketingAccount()) {
            $description .= 'Borgerservice og bibliotekers markedsføringskonto. \\\\ ';
        } else {
            $description .= 'Debitor: '.$orderData->getDebitor().'\\\\ ';
        }
        $description .= ' \\\\ ';

        // Create delivery description.
        $description .= '*Hvor skal ordren leveres?* \\\\ ';
        $description .= $orderData->getDepartment().' \\\\ ';
        $description .= $orderData->getAddress().'\\\\ ';
        $description .= $orderData->getPostalcode().' '.$orderData->getCity().'\\\\ ';
        $description .= 'Dato: '.$orderData->getDate()->format('d-m-Y').'\\\\ ';
        $description .= $orderData->getDeliveryDescription();

        return $description;
    }

    /**
     * Store files locally.
     *
     * @param $gsOrder
     * @param $uploadedFiles
     *
     * @return mixed
     */
    private function storeFile(GsOrder $gsOrder, $form)
    {
        $uploadedFiles = [];
        $upload_files = $form['multi_upload']->getData();
        if ($upload_files) {
            foreach ($upload_files as $file) {
                $uploadedFileName = $this->fileUploader->upload($file);
                $uploadedFiles[] = $uploadedFileName;
            }
        }
        $gsOrder->setFiles($uploadedFiles);

        return $gsOrder;
    }
}
