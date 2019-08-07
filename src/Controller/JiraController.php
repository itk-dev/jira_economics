<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Service\AppService;
use App\Service\MenuService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/jira", name="jira_")
 */
class JiraController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(AppService $appService, MenuService $menuService)
    {
        return $this->render('jira/main/index.html.twig', [
            'apps' => $appService->getApps(),
            'global_menu_items' => $menuService->getGlobalMenuItems(),
        ]);
    }
}
