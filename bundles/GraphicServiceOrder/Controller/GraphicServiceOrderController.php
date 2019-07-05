
<?php
/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */
namespace GraphicServiceOrder\Controller;
use App\Service\JiraService;
use Doctrine\ORM\EntityManagerInterface;
use GraphicServiceOrder\Entity\GsOrder;
use App\Service\OwnCloudService;
use GraphicServiceOrder\Message\OwnCloudShare;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use GraphicServiceOrder\Form\GraphicServiceOrderForm;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use GraphicServiceOrder\Repository\GsOrderRepository;
/**
 * Class GraphicServiceOrderController.
 */
class GraphicServiceOrderController extends AbstractController
{
  private $jiraService;
  private $ownCloudService;
  private $formData;
  private $gsOrderRepository;
  private $appKernel;
  public function __construct(JiraService $jiraService, OwnCloudService $ownCloudService, GsOrderRepository $gsOrderRepository, KernelInterface $appKernel)
  {
    $this->jiraService = $jiraService;
    $this->ownCloudService = $ownCloudService;
    $this->gsOrderRepository = $gsOrderRepository;
    $this->appKernel = $appKernel;
  }
  /**
   * Create a service order.
   *
   * @Route("/new", name="graphic_service_order_form")
   */
  public function createOrder(EntityManagerInterface $entitityManagerInterface, Request $request, MessageBusInterface $bus)
  {
    $gsOrder = new GsOrder();
    $form = $this->createForm(GraphicServiceOrderForm::class, $gsOrder);
    $this->formData = [
      'form' => $form->getData(),
      'accounts' => $this->jiraService->getAllAccounts(),
      'user' => $this->jiraService->getCurrentUser(),
    ];
    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
      // Do stuff on submission.
      // Create a task on a jira project.
      $taskCreated = $this->createOrderTask();
      // Add task values to order entity.
      $gsOrder->setIssueId($taskCreated->id);
      $gsOrder->setIssueKey($taskCreated->key);
      // Store file locally.
      $uploadedFiles = $form->get('files_uploaded')->getData();
      $gsOrder = $this->storeFile($entitityManagerInterface, $form, $gsOrder, $uploadedFiles);
      $gsOrder->setOrderStatus('new');
      $entitityManagerInterface->persist($gsOrder);
      $entitityManagerInterface->flush();
      // --- @todo Move this to messenger  and cleanup -- //
      $newOrders = $this->gsOrderRepository->findBy(['orderStatus' => 'new']);
      // Loop through all orders with unchanged status.
      foreach ($newOrders as $order) {
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
            $files_dir = $this->appKernel->getProjectDir().'/private/files/gs/';
            unlink($files_dir.$file);
          }
        }
        // Update entity.
        $entitityManagerInterface->flush();
        // --- Move this to message END -- //
      }
      // Notify messenger of new job.
      // $this->dispatchMessage(new OwnCloudShare($gsOrder->getId()));
      // Send notification mails
      // @todo
      // Go to form submitted page.
      return $this->redirectToRoute('graphic_service_order_submitted');
    }
    // The initial form build.
    return $this->render('@GraphicServiceOrderBundle/createOrderForm.html.twig', [
      'form' => $form->createView(),
    ]);
  }
  /**
   * Receipt page displayed when an order was created.
   *
   * @Route("/submitted", name="graphic_service_order_submitted")
   */
  public function createOrderSubmitted(JiraService $jiraService, Request $request)
  {
    return $this->render('@GraphicServiceOrderBundle/createOrderSubmitted.html.twig', [
      'form_data' => '',
    ]);
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
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  private function shareFile($fileName, $order_id)
  {
    $ownCloudPath = $_ENV['OWNCLOUD_USER_SHARED_DIR'].$order_id.'/_Materiale/';
    $ocFilename = $order_id.'-'.$fileName;
    $file = file_get_contents('/app/private/files/gs/'.$fileName);
    $response = $this->ownCloudService->sendFile('owncloud/remote.php/dav/files/'.$ownCloudPath.$ocFilename, $file);
    return $response;
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
   * Prepare variables for the receipt page.
   *
   * @param $formData
   *
   * @return mixed
   */
  private function prepareReceiptPage($formData)
  {
    foreach ($_FILES['graphic_service_order_form']['tmp_name']['files'] as $key => $file) {
      if (!empty($file)) {
        $formData['files'][] = $_FILES['graphic_service_order_form']['name']['files'][$key];
      }
    }
    unset($formData['form']->files);
    return $formData;
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
    $description .= 'Dato: '.$formSubmissions->getDate()->format('d-m-Y').'\\\\ ';
    $description .= $formSubmissions->getDeliveryDescription();
    return $description;
  }
  /**
   *  Store files locally.
   *
   * @param $entitityManagerInterface
   * @param $form
   * @param $gsOrder
   *
   * @return mixed
   */
  private function storeFile($entitityManagerInterface, $form, $gsOrder, $uploadedFiles)
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