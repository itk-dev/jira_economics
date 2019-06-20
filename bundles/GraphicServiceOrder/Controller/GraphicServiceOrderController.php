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
      $this->createOrderTask($ownCloudPath);

      // Set variable for receipt page.
      $_SESSION['form_data'] = $this->prepareReceiptPage($this->formData);

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
    $capabilities = $this->ownCloudService->get('owncloud/ocs/v1.php/cloud/config?format=json');
    $ownCloudPath = '';

    return $ownCloudPath;
  }

  private function createOrderTask($ownclodPath) {
    $data = [
      'fields' => [
        'project' => [
          'id' => 10010
        ],
        'summary' => 'ABC test',
        'description' => 'Dette er min beskrivelse',
        'issuetype' => [
          'id' => 10002
        ],
      ]
    ];
    $this->jiraService->post('/rest/api/2/issue', $data);
  }

  private function prepareReceiptPage($formData) {
    foreach ($_FILES['graphic_service_order_form']['tmp_name']['files'] as $key => $file) {
      if (!empty($file)) {
        $formData['files'][] = $_FILES['graphic_service_order_form']['name']['files'][$key];
      }
    }
    unset($formData['form']['files']);
    return $formData;
  }
}
