<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace SprintReport\Controller;

use App\Service\MenuService;
use SprintReport\Service\SprintReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/", methods={"GET"}, name="sprint_report_")
 */
class SprintReportController extends AbstractController
{
    /**
     * @Route("", methods={"GET"}, name="index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectBoard(MenuService $menuService, SprintReportService $sprintReportService)
    {
        $boards = $sprintReportService->getAllBoards();

        return $this->render(
            '@SprintReport/select_board.html.twig',
            [
                'boards' => $boards,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }

    /**
     * @Route("board/{boardId}", methods={"GET"}, name="select_project")
     *
     * @param $boardId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectProject(MenuService $menuService, SprintReportService $sprintReportService, $boardId)
    {
        $projects = $sprintReportService->getAllProjects();
        $board = $sprintReportService->getBoard($boardId);

        return $this->render(
            '@SprintReport/select_project.html.twig',
            [
                'projects' => $projects,
                'boardId' => $boardId,
                'board' => $board,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }

    /**
     * @Route("board/{boardId}/project/{pid}", methods={"GET"}, name="select_version")
     *
     * @param $boardId
     * @param $pid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function selectVersion(MenuService $menuService, SprintReportService $sprintReportService, $boardId, $pid)
    {
        $project = $sprintReportService->getProject($pid);
        $board = $sprintReportService->getBoard($boardId);

        return $this->render(
            '@SprintReport/select_version.html.twig',
            [
                'project' => $project,
                'boardId' => $boardId,
                'board' => $board,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }

    /**
     * @Route("board/{boardId}/version/{vid}", methods={"GET"}, name="sprint_report")
     *
     * @param $boardId
     * @param $vid
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sprintReport(
        MenuService $menuService,
        SprintReportService $sprintReportService,
        $boardId,
        $vid
    ) {
        $sprintReport = $sprintReportService->getSprintReport($vid, $boardId);

        return $this->render(
            '@SprintReport/sprint_report.html.twig',
            [
                'sprintReport' => $sprintReport,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }
}
