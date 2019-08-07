<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

class MenuService
{
    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    protected $requestStack;

    /** @var \Symfony\Component\Routing\RouterInterface */
    protected $router;

    /** @var \App\Service\ContextService */
    protected $contextService;

    /** @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface */
    protected $parameters;

    /**
     * MenuService constructor.
     */
    public function __construct(RequestStack $requestStack, RouterInterface $router, ContextService $contextService, ParameterBagInterface $parameters)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->contextService = $contextService;
        $this->parameters = $parameters;
    }

    public function getMenuItems()
    {
        switch ($this->contextService->getContext()) {
            case ContextService::JIRA:
                return $this->getJiraMenuItems();
        }

        return $this->getPortalMenuItems();
    }

    private function getJiraMenuItems()
    {
        $items = $this->getMenuItemsInContext('jira');

        // @TODO: Check access to menu items.

        return $items;
    }

    private function getPortalMenuItems()
    {
        $items = $this->getMenuItemsInContext('portal');

        // @TODO: Check access to menu items.

        return $items;
    }

    private function getMenuItemsInContext($context)
    {
        if (!$this->parameters->has($context)) {
            return [];
        }

        return $this->parameters->get($context)['menu'] ?? [];
    }

    /**
     * Get global menu items.
     *
     * @return array
     */
    public function getGlobalMenuItems()
    {
        $items = $this->getMenuItems();

        $items = array_filter($items, [$this->contextService, 'isAccessible']);

        foreach ($items as &$item) {
            $item['active'] = $this->contextService->isActiveRoute($item['routeName']);
        }

        return $items;
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
