<?php

namespace GraphicServiceBilling\Controller;

use App\Service\MenuService;
use GraphicServiceBilling\Service\GraphicServiceBillingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
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
     */
    public function index(Request $request, MenuService $menuService, GraphicServiceBillingService $billingService) {
        $formBuilder = $this->createFormBuilder();
        $formBuilder->add('submit', SubmitType::class, []);

        $form = $formBuilder->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $billingService->createExport();
        }

        return $this->render('@GraphicServiceBilling/index.html.twig', [
            'form' => $form->createView(),
            'global_menu_items' => $menuService->getGlobalMenuItems(),
        ]);
    }
}
