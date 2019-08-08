<?php

namespace GraphicServiceOrder\Service;

use App\Service\JiraService;
use App\Service\OwnCloudService;
use Doctrine\ORM\EntityManagerInterface;
use GraphicServiceOrder\Entity\GsOrder;
use GraphicServiceOrder\Message\OwnCloudShareMessage;
use GraphicServiceOrder\Repository\GsOrderRepository;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class OrderService
{
    private $jiraService;
    private $ownCloudService;
    private $formData;
    private $gsOrderRepository;
    private $appKernel;
    private $entityManager;
    private $messageBus;

    /**
     * OrderService constructor.
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\JiraService $jiraService
     * @param \App\Service\OwnCloudService $ownCloudService
     * @param \GraphicServiceOrder\Repository\GsOrderRepository $gsOrderRepository
     * @param \Symfony\Component\HttpKernel\KernelInterface $appKernel
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        JiraService $jiraService,
        OwnCloudService $ownCloudService,
        GsOrderRepository $gsOrderRepository,
        KernelInterface $appKernel,
        MessageBusInterface $messageBus
    ) {
        $this->entityManager = $entityManager;
        $this->gsOrderRepository = $gsOrderRepository;
        $this->jiraService = $jiraService;
        $this->ownCloudService = $ownCloudService;
        $this->gsOrderRepository = $gsOrderRepository;
        $this->appKernel = $appKernel;
        $this->messageBus = $messageBus;
    }

    public function createOrder(GsOrder $gsOrder, $uploadedFiles)
    {
        // Create a task on a jira project.
        $taskCreated = $this->createOrderTask();

        // Add task values to order entity.
        $gsOrder->setIssueId($taskCreated->id);
        $gsOrder->setIssueKey($taskCreated->key);

        // Store file locally.
        $gsOrder = $this->storeFile($gsOrder, $uploadedFiles);
        $gsOrder->setOrderStatus('new');

        $this->entityManager->persist($gsOrder);
        $this->entityManager->flush();

        // Notify messenger of new job.
        $this->messageBus->dispatch(new OwnCloudShareMessage($gsOrder->getId()));

        // Send notification mails
        // @todo
    }


    /**
     * Create a Jira task from a form submission.
     *
     * @return mixed
     */
    private function createOrderTask()
    {
        $formSubmissions = $this->formData['form'];
        $description = $this->getDescription();
        $data = [
            'fields' => [
                'project' => [
                    'id' => $_ENV['GS_ORDER_PROJECT_ID'],
                ],
                'summary' => $formSubmissions->getJobTitle(),
                'description' => $description,
                'issuetype' => [
                    'id' => $_ENV['GS_ORDER_ISSUETYPE_ID'],
                ],
            ],
        ];
        $response = $this->jiraService->post('/rest/api/2/issue', $data);

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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function shareFile($fileName, $order_id)
    {
        $ownCloudPath = $_ENV['OWNCLOUD_USER_SHARED_DIR'].$order_id.'/_Materiale/';
        $ocFilename = $order_id.'-'.$fileName;
        $file = file_get_contents('/app/private/files/gs/'.$fileName);
        $response = $this->ownCloudService->sendFile('owncloud/remote.php/dav/files/'.$ownCloudPath.$ocFilename,
            $file);

        return $response;
    }


    /**
     * Create description for task.
     *
     * @return string
     */
    private function getDescription()
    {
        $formSubmissions = $this->formData['form'];

        // Create task description.
        $description = '*Opgavebeskrivelse* \\\\ ';
        foreach ($formSubmissions->getOrderLines() as $order) {
            $description .= '- '.$order['amount'].' '.$order['type'].'\\\\ ';
        }
        $description .= $formSubmissions->getDescription().'\\\\ ';
        $description .= ' \\\\ ';
        $description .= '[Åbn filer i OwnCloud|https://itkboks.eteket.dk/owncloud/index.php/apps/files/?dir=/Nye%20Ordrer] \\\\ ';
        $description .= ' \\\\ ';

        // Create payment description.
        $description .= '*Hvem skal betale?* \\\\ ';
        if ($formSubmissions->getMarketingAccount()) {
            $description .= 'Borgerservice og bibliotekers markedsføringskonto. \\\\ ';
        } else {
            $description .= 'Debitor: '.$formSubmissions->getDebitor().'\\\\ ';
        }
        $description .= ' \\\\ ';

        // Create delivery description.
        $description .= '*Hvor skal ordren leveres?* \\\\ ';
        $description .= $formSubmissions->getDepartment().' \\\\ ';
        $description .= $formSubmissions->getAddress().'\\\\ ';
        $description .= $formSubmissions->getPostalcode().' '.$formSubmissions->getCity().'\\\\ ';
        $description .= 'Dato: '.$formSubmissions->getDate()
                ->format('d-m-Y').'\\\\ ';
        $description .= $formSubmissions->getDeliveryDescription();

        return $description;
    }

    /**
     *  Store files locally.
     *
     * @param $gsOrder
     *
     * @param $uploadedFiles
     * @return mixed
     */
    private function storeFile($gsOrder, $uploadedFiles)
    {
        $uploadedFiles = explode(';', $uploadedFiles);
        foreach ($uploadedFiles as $key => $file) {
            if (empty($file)) {
                unset($uploadedFiles[$key]);
            }
        }
        $gsOrder->setFiles($uploadedFiles);

        return $gsOrder;
    }
}
