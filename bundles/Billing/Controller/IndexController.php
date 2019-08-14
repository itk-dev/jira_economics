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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
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
