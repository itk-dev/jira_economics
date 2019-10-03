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
use GraphicServiceBilling\Service\GraphicServiceBillingService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\MenuService                  $menuService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function index(Request $request, MenuService $menuService, GraphicServiceBillingService $billingService)
    {
        $startOfWeek = (new \DateTime(date('c', strtotime('this week', time()))))->setTime(0, 0);
        $endOfWeek = (new \DateTime($startOfWeek->format('c')))->add(new \DateInterval('P6D'))->setTime(23, 59);

        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('from', DateTimeType::class, [
            'label' => 'gs_billing_form.from',
            'data' => $startOfWeek,
            'date_widget' => 'single_text',
        ]);
        $formBuilder->add('to', DateTimeType::class, [
            'label' => 'gs_billing_form.to',
            'data' => $endOfWeek,
            'date_widget' => 'single_text',
        ]);
        $formBuilder->add('marketing', CheckboxType::class, [
            'label' => 'gs_billing_form.marketing_account',
            'required' => false,
        ]);
        $formBuilder->add('submit', SubmitType::class, [
            'label' => 'gs_billing_form.preview',
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
            $to = $form->get('to')->getData();
            $marketing = $form->get('marketing')->getData();

            $entries = $billingService->createExportData($from, $to, $marketing);
            $spreadsheet = $billingService->exportInvoicesToSpreadsheet($entries);

            if ($download) {
                $writer = IOFactory::createWriter($spreadsheet, 'Csv');
                $writer->setDelimiter(';');
                $writer->setEnclosure('');
                $writer->setLineEnding("\r\n");
                $writer->setSheetIndex(0);
                $filename = 'faktura'.date('d-m-Y').($marketing ? '-marketing' : '-not_marketing').'-from'.$from->format('d-m-Y').'-to'.$to->format('d-m-Y').'.csv';

                $contentType = 'text/csv';

                $response = new StreamedResponse(
                    function () use ($writer) {
                        $writer->save('php://output');
                    }
                );
                $response->headers->set('Content-Type', $contentType);
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');
                $response->headers->set('Cache-Control', 'max-age=0');

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

        return $this->render('@GraphicServiceBilling/index.html.twig', [
            'form' => $form->createView(),
            'global_menu_items' => $menuService->getGlobalMenuItems(),
            'preview' => $preview ? $preview->saveHTML() : null,
        ]);
    }
}
