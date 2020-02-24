<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceBilling\Controller;

use App\Service\MenuService;
use App\Service\PhpSpreadsheetExportService;
use GraphicServiceBilling\Service\GraphicServiceBillingService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class MainController.
 *
 * @Route("", name="graphic_service_billing_")
 */
class MainController extends AbstractController
{
    /**
     * @Route("", name="index")
     *
     * @param \App\Service\PhpSpreadsheetExportService                    $phpSpreadsheetExportService
     * @param \Symfony\Component\HttpFoundation\Request                   $request
     * @param \App\Service\MenuService                                    $menuService
     * @param \GraphicServiceBilling\Service\GraphicServiceBillingService $graphicServiceBillingService
     * @param $boundProjectId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index(PhpSpreadsheetExportService $phpSpreadsheetExportService, Request $request, MenuService $menuService, GraphicServiceBillingService $graphicServiceBillingService, $boundProjectId)
    {
        $startDayOfWeek = (new \DateTime('this week'))->setTime(0, 0);
        try {
            $endDayOfWeek = (new \DateTime($startDayOfWeek->format('c')))->add(new \DateInterval('P6D'));
        } catch (\Exception $e) {
            throw new HttpException(400, 'Invalid endDayOfWeek.');
        }

        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('from', DateType::class, [
            'label' => 'gs_billing_form.from',
            'data' => $startDayOfWeek,
            'widget' => 'single_text',
        ]);
        $formBuilder->add('to', DateType::class, [
            'label' => 'gs_billing_form.to',
            'data' => $endDayOfWeek,
            'widget' => 'single_text',
        ]);
        $formBuilder->add('marketing', CheckboxType::class, [
            'label' => 'gs_billing_form.marketing_account',
            'required' => false,
        ]);
        $formBuilder->add('markAsBilled', CheckboxType::class, [
            'label' => 'gs_billing_form.mark_as_billed',
            'help' => 'gs_billing_form.mark_as_billed_help',
            'required' => false,
        ]);
        $formBuilder->add('submit', SubmitType::class, [
            'label' => 'gs_billing_form.show_preview',
        ]);
        $formBuilder->add('download', SubmitType::class, [
            'label' => 'gs_billing_form.download',
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

            $marketing = $form->get('marketing')->getData();

            $tasks = $graphicServiceBillingService->getAllNonBilledFinishedTasks($boundProjectId, $from, $to, $marketing);

            $entries = null;

            if ($marketing) {
                $entries = $graphicServiceBillingService->createExportDataMarketing($tasks);
            } else {
                try {
                    $entries = $graphicServiceBillingService->createExportDataNotMarketing($tasks);
                } catch (\Exception $e) {
                    $this->addFlash('danger', $e->getMessage());
                }
            }

            if (null !== $entries) {
                $spreadsheet = $graphicServiceBillingService->exportTasksToSpreadsheet($entries);

                if ($download) {
                    $writer = new Csv($spreadsheet);
                    $writer->setDelimiter(';');
                    $writer->setEnclosure('');
                    $writer->setLineEnding("\r\n");
                    $writer->setSheetIndex(0);
                    $filename = 'faktura'.date('d-m-Y').($marketing ? '-marketing' : '-not_marketing').'-from'.$from->format('d-m-Y').'-to'.$to->format('d-m-Y').'.csv';

                    $csvOutput = $phpSpreadsheetExportService->getOutputAsString($writer);
                    $csvOutputEncoded = mb_convert_encoding($csvOutput, 'Windows-1252');

                    $response = new Response($csvOutputEncoded);
                    $response->headers->set('Content-Type', 'text/csv');
                    $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
                    $response->headers->set('Cache-Control', 'max-age=0');

                    $markAsBilled = $form->get('markAsBilled')->getData();

                    if ($markAsBilled) {
                        $graphicServiceBillingService->markIssuesAsBilled($tasks);
                    }

                    return $response;
                } else {
                    // Show preview.
                    $writer = IOFactory::createWriter($spreadsheet, 'Html');

                    $html = $phpSpreadsheetExportService->getOutputAsString($writer);

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
        }

        return $this->render('@GraphicServiceBilling/index.html.twig', [
            'form' => $form->createView(),
            'global_menu_items' => $menuService->getGlobalMenuItems(),
            'preview' => $preview ? $preview->saveHTML() : null,
        ]);
    }
}
