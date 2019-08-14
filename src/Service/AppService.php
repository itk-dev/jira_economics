<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\User;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AppService
{
    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    private $tokenStorage;

    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var \App\Service\ContextService */
    private $contextService;

    /** @var \Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface */
    private $parameters;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, ContextService $contextService, ParameterBagInterface $parameters)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->contextService = $contextService;
        $this->parameters = $parameters;
    }

    public function getApps()
    {
        switch ($this->contextService->getContext()) {
            case ContextService::JIRA:
                return $this->getAppsInContext('jira');
        }

        return $this->getAppsInContext('portal');
    }

    private function getAppsInContext($context)
    {
        if (!$this->parameters->has($context)) {
            return [];
        }

        $items = $this->parameters->get($context)['apps'] ?? [];
        $items = array_filter($items, [$this->contextService, 'isAccessible']);
        foreach ($items as &$item) {
            $item['active'] = $this->contextService->isActiveRoute($item['routeName']);
        }

        // Keep only enabled apps.
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            $token = $this->tokenStorage->getToken();
            if (null !== $token) {
                /** @var \App\Entity\User $user */
                $user = $token->getUser();
                if ($user instanceof User) {
                    $enabledApps = $user->getPortalApps();
                    $items = array_filter(
                        $items,
                        function ($app) use ($enabledApps) {
                            return \in_array($app, $enabledApps);
                        },
                        ARRAY_FILTER_USE_KEY
                    );
                }
            }
        }

        return $items;
    }
}
