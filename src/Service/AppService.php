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
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
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

    /** @var \App\Service\JiraService */
    private $jiraService;

    /** @var \Symfony\Component\HttpFoundation\Session\SessionInterface */
    private $session;

    public function __construct(TokenStorageInterface $tokenStorage, AuthorizationCheckerInterface $authorizationChecker, ContextService $contextService, JiraService $jiraService, SessionInterface $session, ParameterBagInterface $parameters)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->contextService = $contextService;
        $this->jiraService = $jiraService;
        $this->session = $session;
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
                $enabledApps = [];
                if ($user instanceof User) {
                    $enabledApps = $user->getPortalApps();
                } elseif ($user instanceof OAuthUser) {
                    $enabledApps = $this->getJiraApps($user);
                }
                $items = array_filter(
                    $items,
                    static function ($app) use ($enabledApps) {
                        return \in_array($app, $enabledApps, true);
                    },
                    ARRAY_FILTER_USE_KEY
                );
            }
        }

        return $items;
    }

    /**
     * Get list of Jira apps a user has access to.
     *
     * @param \HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser $user
     *
     * @return array List of apps indexed by app id
     */
    private function getJiraApps(OAuthUser $user)
    {
        $cacheKey = __METHOD__.':'.$user->getUsername();

        if (!$this->session->has($cacheKey)) {
            $jiraUser = $this->jiraService->getCurrentUser(['expand' => 'groups']);
            $userGroups = array_column($jiraUser->groups->items, 'name');
            $jiraApps = array_filter(
                $this->parameters->get('jira_app_groups'),
                static function ($groups) use ($userGroups) {
                    $groups = (array) $groups;

                    return array_intersect($groups, $userGroups);
                }
            );
            $this->session->set($cacheKey, array_combine(array_keys($jiraApps), array_keys($jiraApps)));
        }

        return $this->session->get($cacheKey);
    }

    public function checkAccessToApp(OAuthUser $user, string $app)
    {
        $apps = $this->getJiraApps($user);

        return \array_key_exists($app, $apps);
    }
}
