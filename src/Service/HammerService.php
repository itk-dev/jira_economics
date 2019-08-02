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

class HammerService extends AbstractJiraService
{
    protected $apiUser;

    protected $apiPass;

    /**
     * Constructor.
     */
    public function __construct(
        $jiraUrl,
        $apiUser,
        $apiPass
    ) {
        parent::__construct($jiraUrl);
        $this->apiUser = $apiUser;
        $this->apiPass = $apiPass;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(string $path = '')
    {
        return new Client(
            [
                'base_uri' => $this->jiraUrl,
                'auth' => [
                    $this->apiUser,
                    $this->apiPass,
                ],
            ]
        );
    }
}
