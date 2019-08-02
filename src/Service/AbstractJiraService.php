<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use GuzzleHttp\Exception\RequestException;

abstract class AbstractJiraService
{
    protected $jiraUrl;

    public function __construct($jiraUrl)
    {
        $this->jiraUrl = $jiraUrl;
    }

    /**
     * @return \GuzzleHttp\Client
     */
    abstract protected function getClient(string $path);

    /**
     * Get from Jira.
     *
     * @param $path
     *
     * @return mixed
     */
    public function get($path)
    {
        $client = $this->getClient($path);

        try {
            $response = $client->get($path);

            if ($body = $response->getBody()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * Post to Jira.
     *
     * @param $path
     *
     * @return mixed
     */
    public function post($path, $data)
    {
        $client = $this->getClient($path);

        // Set the "auth" request option to "oauth" to sign using oauth
        try {
            $response = $client->post(
                $path,
                [
                'json' => $data,
                ]
            );

            if ($body = $response->getBody()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * Post to Jira.
     *
     * @param $path
     *
     * @return mixed
     */
    public function put($path, $data)
    {
        $client = $this->getClient($path);

        // Set the "auth" request option to "oauth" to sign using oauth
        try {
            $response = $client->put(
                $path,
                [
                'json' => $data,
                ]
            );

            if ($body = $response->getBody()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * Get project.
     *
     * @param $key
     *   A project key or id
     *
     * @return array
     */
    public function getProject($key)
    {
        $project = $this->get('/rest/api/2/project/'.$key);

        return $project;
    }

    /**
     * Get all projects.
     *
     * @return array
     */
    public function getProjects()
    {
        $projects = [];

        $results = $this->get('/rest/api/2/project');

        foreach ($results as $result) {
            if (!isset($result->projectCategory) || 'Lukket' !== $result->projectCategory->name) {
                $result->url = parse_url($result->self, PHP_URL_SCHEME).'://'.parse_url($result->self, PHP_URL_HOST).'/browse/'.$result->key;
                $projects[] = $result;
            }
        }

        return $projects;
    }

    /**
     * Get all projects, including archived.
     *
     * @return array
     */
    public function getAllProjects()
    {
        $projects = $this->get('/rest/api/2/project');

        return $projects;
    }

    /**
     * Get project categories.
     *
     * @return array
     */
    public function getAllProjectCategories()
    {
        $projectCategories = $this->get('/rest/api/2/projectCategory');

        return $projectCategories;
    }

    /**
     * Get all accounts.
     *
     * @return array
     */
    public function getAllAccounts()
    {
        $accounts = $this->get('/rest/tempo-accounts/1/account/');

        return $accounts;
    }

    /**
     * Get all accounts.
     *
     * @return array
     */
    public function getAllCustomers()
    {
        $accounts = $this->get('/rest/tempo-accounts/1/customer/');

        return $accounts;
    }

    /**
     * Get current user.
     *
     * @return mixed
     */
    public function getCurrentUser()
    {
        $result = $this->get('/rest/api/2/myself');

        return $result;
    }
}
