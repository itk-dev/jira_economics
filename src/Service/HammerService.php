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
     * HammerService constructor.
     *
     * @param $jiraUrl
     * @param $apiUser
     * @param $apiPass
     * @param $customFieldMappings
     */
    public function __construct(
        $jiraUrl,
        $apiUser,
        $apiPass,
        $customFieldMappings
    ) {
        parent::__construct($jiraUrl, $customFieldMappings);
        $this->apiUser = $apiUser;
        $this->apiPass = $apiPass;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient()
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
