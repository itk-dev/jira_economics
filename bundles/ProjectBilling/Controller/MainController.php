<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ProjectBilling\Controller;

use App\Service\MenuService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use ProjectBilling\Service\ProjectBillingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MainController.
 *
 * @Route("", name="project_billing_")
 */
class MainController extends AbstractController
{
    /**
     * @Route("", name="index")
     *
     * @param $boundProjectId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index(Request $request, MenuService $menuService, ProjectBillingService $projectBillingService, $boundProjectId)
    {
        $startDayOfWeek = (new \DateTime('this week'))->setTime(0, 0);
        try {
            $endDayOfWeek = (new \DateTime($startDayOfWeek->format('c')))->add(new \DateInterval('P6D'));
        } catch (\Exception $e) {
            throw new HttpException(400, 'Invalid endDayOfWeek.');
        }

        $projects = $projectBillingService->getProjects();

        $projectOptions = array_reduce($projects, function ($carry, $project) {
            $carry[$project->name] = $project->id;

            return $carry;
        }, []);

        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('from', DateType::class, [
            'label' => 'project_billing_form.from',
            'data' => $startDayOfWeek,
            'widget' => 'single_text',
        ]);
        $formBuilder->add('to', DateType::class, [
            'label' => 'project_billing_form.to',
            'data' => $endDayOfWeek,
            'widget' => 'single_text',
        ]);
        $formBuilder->add('project', ChoiceType::class, [
            'label' => 'project_billing_form.project',
            'choices' => $projectOptions,
            'required' => true,
        ]);
        $formBuilder->add('markAsBilled', CheckboxType::class, [
            'label' => 'project_billing_form.mark_as_billed',
            'help' => 'project_billing_form.mark_as_billed_help',
            'required' => false,
        ]);
        $formBuilder->add('submit', SubmitType::class, [
            'label' => 'project_billing_form.show_preview',
        ]);
        $formBuilder->add('download', SubmitType::class, [
            'label' => 'project_billing_form.download',
            'attr' => [
                'class' => 'btn-secondary',
            ],
        ]);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $preview = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $download = $form->get('download')->isClicked();

            $from = $form->get('from')->getData();
            // Add one day, since all of $to day should be included.
            $to = $form->get('to')->getData()->add(new \DateInterval('P1D'));

            $selectedProject = $form->get('project')->getData();

            $tasks = $projectBillingService->getAllNonBilledFinishedTasks((int) $selectedProject, $from, $to);
            $entries = $projectBillingService->createExportData($tasks);

            $spreadsheet = $projectBillingService->exportTasksToSpreadsheet($entries);

            if ($download) {
                $writer = new Csv($spreadsheet);
                $writer->setDelimiter(';');
                $writer->setEnclosure('');
                $writer->setLineEnding("\r\n");
                $writer->setSheetIndex(0);
                $filename = 'faktura'.date('d-m-Y').'-from'.$from->format('d-m-Y').'-to'.$to->format('d-m-Y').'.csv';

                ob_start();
                $writer->save('php://output');
                $csvOutput = ob_get_clean();

                $csvOutputEncoded = mb_convert_encoding($csvOutput, 'Windows-1252');

                $response = new Response($csvOutputEncoded);
                $response->headers->set('Content-Type', 'text/csv');
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
                $response->headers->set('Cache-Control', 'max-age=0');

                $markAsBilled = $form->get('markAsBilled')->getData();

                if ($markAsBilled) {
                    $projectBillingService->markIssuesAsBilled($tasks);
                }

                return $response;
            } else {
                // Show preview.
                $writer = IOFactory::createWriter($spreadsheet, 'Html');
                ob_start();
                $writer->save('php://output');
                $html = ob_get_clean();

                // Extract body content.
                $d = new \DOMDocument();
                $preview = new \DOMDocument();
                $d->loadHTML($html);
                $body = $d->getElementsByTagName('body')->item(0);
                /* @var \DOMNode $child */
                foreach ($body->childNodes as $child) {
                    if ('style' === $child->tagName) {
                        continue;
                    }
                    if ('table' === $child->tagName) {
                        $child->setAttribute('class', 'table table-bordered');
                    }
                    $preview->appendChild($preview->importNode($child, true));
                }
            }
        }

        return $this->render('@ProjectBilling/index.html.twig', [
            'form' => $form->createView(),
            'global_menu_items' => $menuService->getGlobalMenuItems(),
            'preview' => $preview ? $preview->saveHTML() : null,
        ]);
    }
}
