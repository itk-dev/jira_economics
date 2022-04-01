<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Billing\Controller;

use App\Service\MenuService;
use App\Service\PhpSpreadsheetExportService;
use Billing\Service\BillingService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("JIRA_APP:billing")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/show_export_invoice/{invoiceId}", name="api_show_export_invoice", methods={"GET"})
     *
     * @param \App\Service\PhpSpreadsheetExportService $phpSpreadsheetExportService
     * @param \Billing\Service\BillingService          $billingService
     * @param \App\Service\MenuService                 $menuService
     * @param $invoiceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function showExportInvoice(PhpSpreadsheetExportService $phpSpreadsheetExportService, BillingService $billingService, MenuService $menuService, $invoiceId)
    {
        $spreadsheet = $billingService->exportInvoicesToSpreadsheet([$invoiceId]);

        $writer = IOFactory::createWriter($spreadsheet, 'Html');
        $html = $phpSpreadsheetExportService->getOutputAsString($writer);

        // Extract body content.
        $d = new \DOMDocument();
        $mock = new \DOMDocument();
        $d->loadHTML($html);
        /** @var \DOMNode $body */
        $body = $d->getElementsByTagName('div')->item(0);
        /** @var \DOMNode $child */
        foreach ($body->childNodes as $child) {
            if (isset($child->tagName) && 'style' == $child->tagName) {
                continue;
            }
            if (isset($child->tagName) && 'table' == $child->tagName) {
                $child->setAttribute('class', 'table table-responsive table-bordered');
            }
            $mock->appendChild($mock->importNode($child, true));
        }

        return $this->render('@Billing/table.html.twig', [
            'html' => $mock->saveHTML(),
            'global_menu_items' => $menuService->getGlobalMenuItems(),
        ]);
    }

    /**
     * @Route("/clear_cache", name="clear_cache")
     */
    public function clearCache(BillingService $billingService, MenuService $menuService)
    {
        $success = $billingService->clearCache();

        return $this->render('@BillingBundle/empty.html.twig', [
            'message' => 'Clear cache: '.($success ? 'true' : 'false'),
            'global_menu_items' => $menuService->getGlobalMenuItems(),
        ]);
    }

    /**
     * @Route("/{reactRouting}", name="billing_index", defaults={"reactRouting": null}, requirements={"reactRouting"=".+"})
     */
    public function billing(MenuService $menuService)
    {
        return $this->render('@BillingBundle/index.html.twig', [
            'global_menu_items' => $menuService->getGlobalMenuItems(),
        ]);
    }
}
