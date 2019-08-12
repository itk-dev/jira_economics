<?php


namespace SprintReport\Controller;

use App\Service\MenuService;
use SprintReport\Service\SprintReportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("", methods={"GET"}, name="sprint_report_")
 */
class SprintReportController extends AbstractController
{
    /**
     * @Route("/", methods={"GET"}, name="index")
     * @param \App\Service\MenuService $menuService
     * @param SprintReportService $sprintReportService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sprintReportListAction(MenuService $menuService, SprintReportService $sprintReportService)
    {
        $projects = $sprintReportService->getAllProjects();

        return $this->render(
            '@SprintReport/sprint_report_list.html.twig',
            [
                'projects' => $projects,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }

    /**
     * @Route("/project/{pid}", methods={"GET"}, name="project")
     * @param \App\Service\MenuService $menuService
     * @param SprintReportService $sprintReportService
     * @param $pid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sprintReportAction(MenuService $menuService, SprintReportService $sprintReportService, $pid)
    {
        $project = $sprintReportService->getProject($pid);

        return $this->render(
            '@SprintReport/sprint_report.html.twig',
            [
                'project' => $project,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }

    /**
     * @Route("/version/{vid}", methods={"GET"}, name="version")
     * @param \App\Service\MenuService $menuService
     * @param SprintReportService $sprintReportService
     * @param $vid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sprintReportVersionAction(MenuService $menuService, SprintReportService $sprintReportService, $vid)
    {
        $sprintReport = $sprintReportService->getSprintReport($vid);

        return $this->render(
            '@SprintReport/sprint_report_version.html.twig',
            [
                'sprintReport' => $sprintReport,
                'global_menu_items' => $menuService->getGlobalMenuItems(),
            ]
        );
    }
}
