<?php

namespace GraphicServiceBilling\Controller;

use App\Service\MenuService;
use Billing\Service\BillingService;
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
 * Class MainController
 * @package GraphicServiceBilling\Controller
 *
 * @Route("", name="graphic_service_billing_")
 */
class MainController extends AbstractController
{
    /**
     * @Route("", name="index")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Service\MenuService $menuService
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function index(Request $request, MenuService $menuService, GraphicServiceBillingService $billingService) {
        $now = new \DateTime();

        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('from', DateTimeType::class, [
            'data' => $now,
            'date_widget' => 'single_text',
        ]);
        $formBuilder->add('to', DateTimeType::class, [
            'data' => $now,
            'date_widget' => 'single_text',
        ]);
        $formBuilder->add('marketing', CheckboxType::class, [
            'required' => false,
        ]);
        $formBuilder->add('submit', SubmitType::class, []);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        $mock = null;

        if ($form->isSubmitted() && $form->isValid()) {
            $from = $form->get('from')->getData();
            $to = $form->get('to')->getData();
            $marketing = $form->get('marketing')->getData();

            $entries = $billingService->createExportData($from, $to, $marketing);
            $spreadsheet = $billingService->exportInvoicesToSpreadsheet($entries);

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
                    $child->setAttribute('class', 'table table-bordered');
                }
                $mock->appendChild($mock->importNode($child, true));
            }
        }

        return $this->render('@GraphicServiceBilling/index.html.twig', [
            'form' => $form->createView(),
            'global_menu_items' => $menuService->getGlobalMenuItems(),
            'export' => $mock ? $mock->saveHTML() : '',
        ]);
    }
}
