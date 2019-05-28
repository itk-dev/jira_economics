<?php

namespace CreateProject\Controller;

use App\Service\JiraService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use CreateProject\Form\CreateProjectForm;

use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CreateProjectController
 */
class CreateProjectController extends Controller
{
  private $jiraService;
  private $formData;

  public function __construct(JiraService $jiraService)
  {
    $this->jiraService = $jiraService;
  }

  /**
   * @Route("/new", name="create_project_form")
   */
  public function createProject(Request $request)
  {
    $form = $this->createForm(CreateProjectForm::class);
    $form->handleRequest($request);

    $this->formData = [
      'form' => $form->getData(),
      'projects' => $this->jiraService->getAllProjects(),
      'accounts' => $this->jiraService->getAllAccounts(),
      'projectCategories' => $this->jiraService->getAllProjectCategories(),
      'allTeamsConfig' => $this->getParameter('teamconfig'),
    ];

    if ($form->isSubmitted() && $form->isValid()) {
      // Do stuff on submission.
      foreach ($this->formData['projectCategories'] as $team) {
        if ($team->id == $this->formData['form']['team']) {
          $this->formData['selectedTeamConfig'] = $this->formData['allTeamsConfig'][$team->name];
        }
      }
      
      if ($this->formData['form']['new_account']) {
        // Define account customer.
        if ($this->formData['form']['new_customer']) {
          // Create customer.
          $customer = $this->createJiraCustomer();

          // Set customer key from new customer.
          $this->formData['customer_key'] = $customer->key;
        }
        else {
          foreach ($this->formData['accounts'] as $account) {
            if ($account->key == $this->formData['form']['account']) {
              // Set customer key from selected customer.
              $this->formData['customer_key'] = $account->customer->key;
            }
          }
        }

        // Create account if new account is selected.
        $this->createJiraAccount();
      }

      // Create project
      $this->createJiraProject();

      // Add project to tempo team
      $this->addProjectToTeam();

      // Add project to tempo account
      $this->addProjectToAccount();

      // Create project board
      $this->createProjectBoard();

      // Go to form submitted page.
      return $this->redirectToRoute('create_project_submitted');
    }

    // The initial form build.
    return $this->render('@CreateProjectBundle/createProjectForm.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  /**
   * @Route("/submitted", name="create_project_submitted")
   */
  public function createProjectSubmitted(JiraService $jiraService, Request $request)
  {
    // The initial form build.
    return $this->render('@CreateProjectBundle/createProjectSubmitted.html.twig');
  }

  private function createJiraCustomer()
  {
    $customer = [
      'isNew' => 1,
      'name' => $this->formData['form']['new_customer_name'],
      'key' => preg_replace('@[^a-z0-9_]+@', '_', strtolower($this->formData['form']['new_customer_name'])),
    ];
    $response = $this->jiraService->post('rest/tempo-accounts/1/customer/', $customer);

    return $response;
  }

  private function createJiraAccount()
  {
    // Note! Price tables (rateTable) do not seem to work with the api at the moment. 23.05.2019
    // Even though they are included @ http://developer.tempo.io/doc/accounts/api/rest/latest/
    
    // - Remember to add project link
    $account = [
      'name' => $this->formData['form']['new_account_name'],
      'key' => preg_replace('@[^a-z0-9_]+@', '_', strtolower($this->formData['form']['new_account_name'])),
      'status' => 'OPEN',
      'category' => array(
        'key' => 'DRIFT',
      ),
      'customer' => array(
        'key' => $this->formData['customer_key'],
      ),
      'contact' => array(
        'username' => $this->formData['form']['new_account_contact']
      ),
      'lead' => array(
        'username' => $_ENV['CPB_ACCOUNT_MANAGER']
      ),
    ];
    $response = $this->jiraService->post('rest/tempo-accounts/1/account/', $account);
  }

  private function createJiraProject()
  {
    // Missing:
    // Cleanup of workflow, issuetypes and screen. (Make robot @Anders)
    $projectKey = strtoupper($this->formData['form']['project_key']);
    $project = [
      'key' => $projectKey,
      'name' => $this->formData['form']['project_name'],
      'lead' => $this->formData['selectedTeamConfig']['team_lead'],
      'typeKey' => 'software',
      'templateKey' => 'com.pyxis.greenhopper.jira:basic-software-development-template', //https://community.atlassian.com/t5/Answers-Developer-Questions/JIRA-API-7-1-0-Create-Project/qaq-p/551444
      'description' => $this->formData['form']['description'],
      'assigneeType' => 'UNASSIGNED',
      'categoryId' => $this->formData['selectedTeamConfig']['project_category'],
      'issueTypeScheme' => $this->formData['selectedTeamConfig']['issue_type_scheme'],
      'workflowScheme' => $this->formData['selectedTeamConfig']['workflow_scheme'],
      'issueTypeScreenScheme' => $this->formData['selectedTeamConfig']['issue_type_screen_scheme'],
      'permissionScheme' => $this->formData['selectedTeamConfig']['permission_scheme'],
    ];

    $response = $this->jiraService->post('rest/extender/1.0/project/createProject', $project);
    // https://docs.atlassian.com/software/jira/docs/api/REST/8.1.0/#api/2/project-createProject
    // - Remember to fill account field on project
  }

  private function addProjectToTeam()
  {

  }

  private function addProjectToAccount()
  {

  }

  private function createProjectBoard()
  {

  }
}
