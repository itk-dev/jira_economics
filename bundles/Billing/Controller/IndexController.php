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
use Billing\Service\BillingService;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/show_export_invoice/{invoiceId}", name="api_show_export_invoice", methods={"GET"})
     *
     * @param \Billing\Service\BillingService $billingService
     * @param $invoiceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function showExportInvoice(BillingService $billingService, MenuService $menuService, $invoiceId)
    {
        $spreadsheet = $billingService->exportInvoicesToSpreadsheet([$invoiceId]);

        $writer = IOFactory::createWriter($spreadsheet, 'Html');
        ob_start();
        $writer->save('php://output');
        $html = ob_get_clean();

        // Extract body content.
        $d = new \DOMDocument();
        $mock = new \DOMDocument();
        $d->loadHTML($html);
        $body = $d->getElementsByTagName('body')->item(0);
        foreach ($body->childNodes as $child) {
            if ('style' === $child->tagName) {
                continue;
            }
            if ('table' === $child->tagName) {
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
     * @Route("/{reactRouting}", name="billing_index", defaults={"reactRouting": null}, requirements={"reactRouting"=".+"})
     */
    public function billing(MenuService $menuService)
    {
        return $this->render('@BillingBundle/index.html.twig', [
            'global_menu_items' => $menuService->getGlobalMenuItems(),
        ]);
    }
}
