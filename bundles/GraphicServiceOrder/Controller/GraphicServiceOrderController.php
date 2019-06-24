<?php

namespace GraphicServiceOrder\Controller;

use App\Service\JiraService;
use App\Service\ownCloudService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use GraphicServiceOrder\Form\GraphicServiceOrderForm;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GraphicServiceOrderController
 */
class GraphicServiceOrderController extends Controller
{
  private $jiraService;
  private $ownCloudService;
  private $formData;

  public function __construct(JiraService $jiraService, ownCloudService $ownCloudService)
  {
    $this->jiraService = $jiraService;
    $this->ownCloudService = $ownCloudService;
  }

  /**
   * Create a service order.
   *
   * @Route("/new", name="graphic_service_order_form")
   */
  public function createOrder(Request $request)
  {
    $form = $this->createForm(GraphicServiceOrderForm::class);
    $form->handleRequest($request);

    $this->formData = [
      'form' => $form->getData(),
      'accounts' => $this->jiraService->getAllAccounts(),
      'user' => $this->jiraService->getCurrentUser(),
    ];

    if ($form->isSubmitted() && $form->isValid()) {
      // Do stuff on submission.

      // Save a file to Own Cloud.
      $ownCloudPath = $this->storeFile();

      // Create a task on a jira project.
      //$this->createOrderTask($ownCloudPath);

      // Set variable for receipt page and email.
      $_SESSION['form_data'] = $this->prepareReceiptPage($this->formData);

      // Send receipt email.
      //$this->sendReceiptEmail($_SESSION['form_data']);

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
      'form_data' => $_SESSION['form_data'],
    ]);
  }

  private function storeFile() {
    $capabilities = $this->ownCloudService->get('owncloud/ocs/v1.php/cloud/capabilities?format=json');
    $shared_with_me = $this->ownCloudService->get('owncloud/ocs/v1.php/apps/files_sharing/api/v1/shares?shared_with_me=true&format=json');
    $shares = $this->ownCloudService->get('owncloud/ocs/v1.php/apps/files_sharing/api/v1/shares?path=/Grafisk Design&subfiles=true&shared_with_me=true&format=json');
    $ownCloudPath = '';

    return $ownCloudPath;
  }

  /**
   * Create a Jira task from a form submission.
   *
   * @param $owncloudPath
   */
  private function createOrderTask($owncloudPath) {
    $formSubmissions = $this->formData['form'];
    $description = $this->getDescription();
    $data = [
      'fields' => [
        'project' => [
          'id' => 10010
        ],
        'summary' => $formSubmissions['job_title'],
        'description' => $description,
        'issuetype' => [
          'id' => 10002
        ],
      ]
    ];
    $this->jiraService->post('/rest/api/2/issue', $data);
  }

  /**
   * Prepare variables for the receipt page.
   *
   * @param $formData
   * @return mixed
   */
  private function prepareReceiptPage($formData) {
    foreach ($_FILES['graphic_service_order_form']['tmp_name']['files'] as $key => $file) {
      if (!empty($file)) {
        $formData['files'][] = $_FILES['graphic_service_order_form']['name']['files'][$key];
      }
    }
    unset($formData['form']['files']);
    return $formData;
  }

  /**
   * Create description for task.
   *
   * @return string
   */
  private function getDescription() {
    $formSubmissions = $this->formData['form'];

    // Create task description.
    $description = '*Opgavebeskrivelse* \\\\ ';
    foreach ($formSubmissions['order_lines'] as $order) {
      $description .= '- ' . $order['amount'] . ' ' . $order['type'] . '\\\\ ';
    }
    $description .=  $formSubmissions['description'] . '\\\\ ' ;
    $description .= ' \\\\ ';
    $description .=  '[Åbn filer i OwnCloud|http://google.com] \\\\ ';
    $description .= ' \\\\ ';

    // Create payment description.
    $description .= '*Hvem skal betale?* \\\\ ';
    if ($formSubmissions['marketing_account']) {
      $description .= 'Borgerservice og bibliotekers markedsføringskonto. \\\\ ';
    } else {
      $description .= 'Debitor: ' . $formSubmissions['debitor'] . '\\\\ ';
    }
    $description .= ' \\\\ ';

    // Create delivery description.
    $description .= '*Hvor skal ordren leveres?* \\\\ ';
    $description .= $formSubmissions['department'] . ' \\\\ ';
    $description .= $formSubmissions['address'] . '\\\\ ';
    $description .= $formSubmissions['postal_code'] . ' ' . $formSubmissions['city'] . '\\\\ ';
    $description .= 'Dato: ' . $formSubmissions['date']->format('d-m-Y') . '\\\\ ';
    $description .= $formSubmissions['delivery_description'];

    return $description;
  }
}
