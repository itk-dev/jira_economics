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
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ContextService
{
    public const JIRA = 'jira';
    public const PORTAL = 'portal';

    /** @var \Symfony\Component\HttpFoundation\RequestStack */
    private $requestStack;

    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(RequestStack $requestStack, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->requestStack = $requestStack;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function getContext()
    {
        $request = $this->requestStack->getCurrentRequest();
        $path = $request->getPathInfo();

        if (0 === strpos($path, '/jira')) {
            return self::JIRA;
        }

        return self::PORTAL;
    }

    public function isActiveRoute($route)
    {
        $request = $this->requestStack->getCurrentRequest();
        $currentRoute = $request->attributes->get('_route');

        // @TODO Improve this check.
        return 0 === strpos($currentRoute, $route);
    }

    public function isAccessible(array $item)
    {
        if (isset($item['roles'])) {
            $roles = (array) $item['roles'];
            if (!$this->authorizationChecker->isGranted($roles)) {
                return false;
            }
        }

        // @TODO Check user access to portal app. Use voter?

        return true;
    }
}
