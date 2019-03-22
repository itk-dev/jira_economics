<?php

namespace App\Controller;

use App\Service\JiraService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ApiController
 * @package App\Controller
 *
 * @Route("/jira_api")
 */
class ApiController extends Controller
{
    /**
     * @Route("/project/{token}", name="api_project")
     * defaults={"token"="...."})
     */
    public function projectAction(JiraService $jiraService, Request $request)
    {
        $jiraProjectId = $request->get('token');
        $result = $jiraService->getProject($jiraProjectId);
        return new JsonResponse($result);
    }

    /**
     * @Route("/projects", name="api_projects")
     */
    public function projectsAction(JiraService $jiraService)
    {
        return new JsonResponse($jiraService->getProjects());
    }

    /**
     * @Route("/current_user", name="api_current_user")
     */
    public function currentUserAction(JiraService $jiraService)
    {
        return new JsonResponse($jiraService->getCurrentUser());
    }

    /**
     * @Route("/project/{jiraId}", name="api_project")
     */
    public function projectAction(JiraService $jiraService, $jiraId)
    {
        return new JsonResponse(['jiraId' => $jiraId, 'name' => 'TestProject']);
    }
}
