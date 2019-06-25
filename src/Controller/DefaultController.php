<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Service\MenuService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="index")
     */
    public function index(MenuService $menuService)
    {
        $apps = [
            'planning' => (object) [
                'title' => 'Planlægning',
                'desc' => 'Planlægningsoversigt baseret på tasks i Jira',
                'icon' => 'fa-braille',
                'routeName' => 'planning_index',
                'btnTxt' => 'Se planlægning',
            ],
            'newProject' => (object) [
                'title' => 'Nyt projekt',
                'desc' => 'Opret et nyt Jira-projekt ud fra en skabelon',
                'icon' => 'fa-project-diagram',
                'routeName' => 'create_project_form',
                'btnTxt' => 'Opret Nyt projekt',
            ],
            'invoice' => (object) [
                'title' => 'Faktura',
                'desc' => 'Opret og rediger fakturaer baseret på tasks i Jira',
                'icon' => 'fa-file-invoice',
                'routeName' => 'billing_index',
                'btnTxt' => 'Lav en Faktura',
            ],
            'expense' => (object) [
                'title' => 'Udgift',
                'desc' => 'Opret udgifter i forbindelse med projekter og tasks',
                'icon' => 'fa-credit-card',
                'routeName' => 'expense_new',
                'btnTxt' => 'Opret en udgift',
            ],

            // @TODO: Add when ready.
            /*
            'taskWizard' => (object) [
                'title' => 'Task wizard',
                'desc' => 'Opret mange tasks på en gang.',
                'icon' => 'fa-tasks',
                'routeName' => 'index',
                'btnTxt' => 'Bliv Task wizard',
            ],
            'order' => (object) [
                'title' => 'Ordre',
                'desc' => 'Opret ordrepakker ud fra tasks i Jira',
                'icon' => 'fa-box-open',
                'routeName' => 'index',
                'btnTxt' => 'Pak en Ordre',
            ],
            'sprintplan' => (object) [
                'title' => 'Sprintrapport',
                'desc' => 'Generér sprintrapport',
                'icon' => 'fa-braille',
                'routeName' => 'index',
                'btnTxt' => 'Se sprintrapporter',
            ],
            */
        ];

        return $this->render('main/dashboard.html.twig', [
            'apps' => $apps,
            'global_menu_items' => $menuService->getGlobalMenuItems(),
        ]);
    }
}
