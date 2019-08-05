<?php

namespace Planning\Controller;

use Planning\Service\PlanningService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PlanningController.
 *
 * @Route("/")
 */
class PlanningController extends AbstractController
{
    /**
     * @Route("/")
     */
    public function planningOverviewAction() {
        $jiraUrl = getenv('JIRA_URL');

        return $this->render(
            '@PlanningBundle/planning.html.twig',
            [
                'jiraUrl' => $jiraUrl,
            ]
        );
    }

    /**
     * @Route("/future_sprints")
     */
    public function futureSprints(PlanningService $planningService) {
        $sprints = $planningService->getFutureSprints();

        return new JsonResponse(['sprints' => $sprints]);
    }

    /**
     * @Route("/issues/{sprintId}")
     */
    public function issuesInSprint(PlanningService $planningService, $sprintId) {
        $issues = $planningService->getIssuesInSprint($sprintId);

        return new JsonResponse(['issues' => $issues]);
    }
}
