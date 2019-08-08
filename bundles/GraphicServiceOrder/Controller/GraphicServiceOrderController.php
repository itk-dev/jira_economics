<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceOrder\Controller;

use App\Service\JiraService;
use GraphicServiceOrder\Entity\GsOrder;
use GraphicServiceOrder\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use GraphicServiceOrder\Form\GraphicServiceOrderForm;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GraphicServiceOrderController.
 */
class GraphicServiceOrderController extends AbstractController
{
    /**
     * Create a service order.
     *
     * @Route("/", name="graphic_service_order_form")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \GraphicServiceOrder\Service\OrderService $orderService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createOrder(Request $request, OrderService $orderService, JiraService $jiraService)
    {
        $gsOrder = new GsOrder();
        $form = $this->createForm(GraphicServiceOrderForm::class, $gsOrder);

        $this->formData = [
            'form' => $form->getData(),
            'accounts' => $jiraService->getAllAccounts(),
            'user' => $jiraService->getCurrentUser(),
        ];

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFiles = $form->get('files_uploaded')->getData();
            $orderService->createOrder($gsOrder, $uploadedFiles);

            // Go to form submitted page.
            return $this->redirectToRoute('graphic_service_order_submitted');
        }

        // The initial form build.
        return $this->render('@GraphicServiceOrderBundle/createOrderForm.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Receipt page displayed when an order was created.
     *
     * @Route("/submitted", name="graphic_service_order_submitted")
     */
    public function createOrderSubmitted()
    {
        return $this->render('@GraphicServiceOrderBundle/createOrderSubmitted.html.twig');
    }
}
