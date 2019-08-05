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
    public function boardAction(PlanningService $planningService)
    {
        $boards = $planningService->getAllBoards();

        return $this->render(
            '@PlanningBundle/board.html.twig',
            [
                'boards' => $boards,
            ]
        );
    }

    /**
     * @Route("/board")
     */
    public function allBoards(PlanningService $planningService) {
        $boards = $planningService->getAllBoards();

        return new JsonResponse(['boards' => $boards]);
    }

    /**
     * @Route("/board/{boardId}")
     * @param null $boardId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function planningOverviewAction(PlanningService $planningService, $boardId = null) {
        $jiraUrl = getenv('JIRA_URL');

        if ($boardId == null) {
            $boardId = getenv('JIRA_DEFAULT_BOARD');
        }

        $board = $planningService->getBoard($boardId);

        return $this->render(
            '@PlanningBundle/planning.html.twig',
            [
                'jiraUrl' => $jiraUrl,
                'board' => $board,
            ]
        );
    }

    /**
     * @Route("/board/{boardId}/future_sprints")
     */
    public function futureSprints(PlanningService $planningService, $boardId) {
        $sprints = $planningService->getFutureSprints($boardId);

        return new JsonResponse(['sprints' => $sprints]);
    }

    /**
     * @Route("/board/{boardId}/issues/{sprintId}")
     */
    public function issuesInSprint(PlanningService $planningService, $boardId, $sprintId) {
        $issues = $planningService->getIssuesInSprint($boardId, $sprintId);

        return new JsonResponse(['issues' => $issues]);
    }
}
