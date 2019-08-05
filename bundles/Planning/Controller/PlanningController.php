<?php

namespace Planning\Controller;

use Planning\Service\PlanningService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class PlanningController.
 *
 * @Route("/planning")
 */
class PlanningController extends AbstractController
{
    /**
     * @Route("/")
     * @Method("GET")
     */
    public function planningOverviewAction() {
        return $this->render(
            '@PlanningBundle/planning.html.twig'
        );
    }

    /**
     * @Route("/future_sprints")
     * @Method("GET")
     */
    public function futureSprints(PlanningService $planningService) {
        $sprints = $planningService->getFutureSprints();

        return new JsonResponse(['sprints' => $sprints]);
    }

    /**
     * @Route("/issues/{sprintId}")
     * @Method("GET")
     */
    public function issuesInSprint(PlanningService $planningService, $sprintId) {
        $issues = $planningService->getIssuesInSprint($sprintId);

        return new JsonResponse(['issues' => $issues]);
    }
}
