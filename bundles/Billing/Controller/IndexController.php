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
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @IsGranted("JIRA_APP:billing")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/show_export_invoice/{invoiceId}", name="api_show_export_invoice", methods={"GET"})
     *
     * @param \Symfony\Component\HttpKernel\KernelInterface $kernel
     * @param \Billing\Service\BillingService               $billingService
     * @param \App\Service\MenuService                      $menuService
     * @param $invoiceId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function showExportInvoice(KernelInterface $kernel, BillingService $billingService, MenuService $menuService, $invoiceId)
    {
        $spreadsheet = $billingService->exportInvoicesToSpreadsheet([$invoiceId]);

        $writer = IOFactory::createWriter($spreadsheet, 'Html');

        $filesystem = new Filesystem();
        $filesystem->mkdir($kernel->getProjectDir().'/var/tmp_files/');
        $tempFilename = $kernel->getProjectDir().'/var/tmp_files/export'.sha1(microtime());

        // Save to temp file.
        $writer->save($tempFilename);

        $html = file_get_contents($tempFilename);

        $filesystem->remove($tempFilename);

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
