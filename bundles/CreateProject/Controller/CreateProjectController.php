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
  /**
   * @Route("/new", name="create_project_form")
   */
  public function createProject(JiraService $jiraService, Request $request)
  {

    $form = $this->createForm(CreateProjectForm::class);
    $form->handleRequest($request);
    /*
    $variables = $form->getData();
    // Check for project name/id validation on submit.
    $existingProjects = $jiraService->getAllProjects();

    if ($form->isSubmitted() && $form->isValid()) {
      // Do stuff on submission.

      // Check for project name/id validation on submit.
      $existingProjects = $jiraService->getAllProjects();

      // Check for account validation on submit.
      $accounts = $jiraService->getAllAccounts();

      // Go to form submitted page.
      return $this->redirectToRoute('create_project_submitted');
    }

    */
    // The initial form build.
    return $this->render('createProject/forms/createProjectForm.html.twig', [
      'form' => $form->createView(),
    ]);
  }

  /**
   * @Route("/submitted", name="create_project_submitted")
   */
  public function createProjectSubmitted(JiraService $jiraService, Request $request)
  {
    // The initial form build.
    return $this->render('createProject/content/createProjectSubmitted.html.twig');
  }
}
