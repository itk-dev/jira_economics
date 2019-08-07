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
                return $this->getJiraApps();
        }

        return $this->getPortalApps();
    }

    private function getJiraApps()
    {
        $apps = $this->getAppsInContext('jira');

        // @TODO: Check access to apps.

        return $apps;
    }

    private function getPortalApps()
    {
        $apps = $this->getAppsInContext('portal');

        // @TODO: Check access to apps.

        return $apps;
    }

    private function getAppsInContext($context)
    {
        if (!$this->parameters->has($context)) {
            return [];
        }

        return $this->parameters->get($context)['apps'] ?? [];
    }
}
