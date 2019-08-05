<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use GuzzleHttp\Client;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class JiraService extends AbstractJiraService
{
    protected $tokenStorage;
    protected $customerKey;
    protected $pemPath;

    /**
     * Constructor.
     */
    public function __construct(
        $jiraUrl,
        $tokenStorage,
        $customerKey,
        $pemPath
    ) {
        parent::__construct($jiraUrl);
        $this->tokenStorage = $tokenStorage;
        $this->customerKey = $customerKey;
        $this->pemPath = $pemPath;
        $this->jiraUrl = $jiraUrl;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient()
    {
        $token = $this->tokenStorage->getToken();

        if ($token instanceof AnonymousToken) {
            throw new HttpException(401, 'unauthorized');
        }

        return new Client([
            'base_uri' => $this->jiraUrl,
        ]);
    }

    protected function getConfiguration()
    {
        $configuration = [
            'jiraLogEnabled' => false,
            'jiraHost' => $this->jiraUrl,
        ];

        $token = $this->tokenStorage->getToken();
        if ($token instanceof OAuthToken) {
            $configuration['token'] = $token;
            $configuration['authorizationConfiguration'] = [
                'customerKey' => $this->customerKey,
                'pemPath' => $this->pemPath,
                'jiraUrl' => $this->jiraUrl.'/rest/api/2',
            ];
        }

        return new JiraConfiguration($configuration);
    }
}
