<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Planning\Controller;

use App\Service\MenuService;
use Planning\Service\PlanningService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PlanningController.
 *
 * @Route("/", name="planning_")
 */
class PlanningController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PlanningService $planningService, MenuService $menuService)
    {
        $boards = $planningService->getAllBoards();

        return $this->render(
            '@PlanningBundle/board.html.twig',
            [
                'boards' => $boards,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }

    /**
     * @Route("/board", name="boards")
     */
    public function allBoards(PlanningService $planningService)
    {
        $boards = $planningService->getAllBoards();

        return new JsonResponse(['boards' => $boards]);
    }

    /**
     * @Route("/board/{boardId}", name="board")
     *
     * @param null $boardId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function planningOverview(PlanningService $planningService, MenuService $menuService, $boardId = null)
    {
        $jiraUrl = getenv('JIRA_URL');

        if (null === $boardId) {
            $boardId = getenv('JIRA_DEFAULT_BOARD');
        }

        $board = $planningService->getBoard($boardId);

        return $this->render(
            '@PlanningBundle/planning.html.twig',
            [
                'jiraUrl' => $jiraUrl,
                'board' => $board,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }

    /**
     * @Route("/board/{boardId}/future_sprints", name="future_sprints")
     */
    public function futureSprints(PlanningService $planningService, $boardId)
    {
        $sprints = $planningService->getFutureSprints($boardId);

        return new JsonResponse(['sprints' => $sprints]);
    }

    /**
     * @Route("/board/{boardId}/issues/{sprintId}", name="issues")
     */
    public function issuesInSprint(PlanningService $planningService, $boardId, $sprintId)
    {
        $issues = $planningService->getIssuesInSprint($boardId, $sprintId);

        return new JsonResponse(['issues' => $issues]);
    }
}
