<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class MenuService
{
    protected $requestStack;
    protected $router;

    /**
     * MenuService constructor.
     */
    public function __construct(RequestStack $requestStack, RouterInterface $router)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
    }

    /**
     * Get global menu items.
     *
     * @return array
     */
    public function getGlobalMenuItems()
    {
        $pathInfo = $this->requestStack->getCurrentRequest()->getPathInfo();

        $globalMenu = [
            'dash' => [
                'title' => 'Dash',
                'desc' => 'App oversigt',
                'icon' => 'fa-th-large',
                'routeName' => 'index',
                'active' => '/' === $pathInfo,
            ],
            'planning' => (object) [
                'title' => 'Planlægning',
                'desc' => 'Planlægningsoversigt baseret på tasks i Jira',
                'icon' => 'fa-braille',
                'routeName' => 'planning_index',
                'active' => $this->routeStartsWith('planning_index', $pathInfo),
            ],
            'newProject' => (object) [
                'title' => 'Nyt projekt',
                'desc' => 'Opret et nyt Jira-projekt ud fra en skabelon',
                'icon' => 'fa-project-diagram',
                'routeName' => 'create_project_form',
                'active' => $this->routeStartsWith('create_project_form', $pathInfo),
            ],
            'invoice' => (object) [
                'title' => 'Faktura',
                'desc' => 'Opret og rediger fakturaer baseret på tasks i Jira',
                'icon' => 'fa-file-invoice',
                'routeName' => 'billing_index',
                'active' => $this->routeStartsWith('billing_index', $pathInfo),
            ],


            // @TODO: Add when ready.
            /*
            'taskWizard' => (object) [
                'title' => 'Task wizard',
                'desc' => 'Opret mange tasks på en gang.',
                'icon' => 'fa-tasks',
                'routeName' => 'index',
                'active' => false,
            ],
            'order' => (object) [
                'title' => 'Ordre',
                'desc' => 'Opret ordrepakker ud fra tasks i Jira',
                'icon' => 'fa-box-open',
                'routeName' => 'index',
                'active' => false,
            ],
            'expenses' => (object) [
                'title' => 'Udgift',
                'desc' => 'Opret udgifter i forbindelse med projekter og tasks',
                'icon' => 'fa-credit-card',
                'routeName' => 'index',
                'active' => false,
            ],
            'sprintplan' => (object) [
                'title' => 'Sprintrapport',
                'desc' => 'Generér sprintrapport',
                'icon' => 'fa-braille',
                'routeName' => 'index',
                'active' => false,
            ],
            */
        ];

        return $globalMenu;
    }

    /**
     * Test if route url starts with path.
     *
     * @param $routeName
     * @param $path
     *
     * @return bool
     */
    private function routeStartsWith($routeName, $path)
    {
        $route = $this->router->generate($routeName);
        $length = \strlen($route);

        return substr($path, 0, $length) === $route;
    }
}
