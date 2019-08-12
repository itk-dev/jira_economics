<?php


namespace SprintReport\Controller;

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
     * @param SprintReportService $sprintReportService
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sprintReportListAction(SprintReportService $sprintReportService)
    {
        $projects = $sprintReportService->getAllProjects();

        return $this->render(
            '@SprintReport/sprint_report_list.html.twig',
            [
                'projects' => $projects,
            ]
        );
    }

    /**
     * @Route("/project/{pid}", methods={"GET"}, name="project")
     * @param SprintReportService $sprintReportService
     * @param $pid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sprintReportAction(SprintReportService $sprintReportService, $pid)
    {
        $project = $sprintReportService->getProject($pid);

        return $this->render(
            '@SprintReport/sprint_report.html.twig',
            [
                'project' => $project,
            ]
        );
    }

    /**
     * @Route("/version/{vid}", methods={"GET"}, name="version")
     * @param SprintReportService $sprintReportService
     * @param $vid
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sprintReportVersionAction(SprintReportService $sprintReportService, $vid)
    {
        $sprintReport = $sprintReportService->getSprintReport($vid);

        return $this->render(
            '@SprintReport/sprint_report_version.html.twig',
            $sprintReport
        );
    }

}
