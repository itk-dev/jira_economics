<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\Controller;

use GraphicServiceOrder\Entity\GsOrder;
use GraphicServiceOrder\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use GraphicServiceOrder\Form\GraphicServiceOrderForm;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GraphicServiceOrderController.
 *
 * @Route("/", name="graphic_service_order_")
 */
class GraphicServiceOrderController extends AbstractController
{
    /**
     * Create a service order.
     *
     * @Route("/", name="form")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \GraphicServiceOrder\Service\OrderService $orderService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createOrder(Request $request, OrderService $orderService)
    {
        $gsOrder = $orderService->prepareOrder();
        $form = $this->createForm(GraphicServiceOrderForm::class, $gsOrder);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $orderService->createOrder($gsOrder, $form);

            // Go to form submitted page.
            return $this->redirectToRoute('graphic_service_order_submitted', [$gsOrder->getId()]);
        }

        // The initial form build.
        return $this->render('@GraphicServiceOrderBundle/createOrderForm.html.twig', [
            'form' => $form->createView(),
            'user_email' => $orderService->getUserEmail(),
        ]);
    }

    /**
     * Receipt page displayed when an order was created.
     *
     * @Route("/submitted", name="submitted")
     */
    public function createOrderSubmitted()
    {
        $order = $this->getDoctrine()->getRepository(GsOrder::class)->find($_REQUEST[0]);

        return $this->render('@GraphicServiceOrderBundle/createOrderSubmitted.html.twig', [
            'order' => $order,
        ]);
    }
}
