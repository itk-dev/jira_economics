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
use JiraRestApi\Project\ProjectService;

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
    abstract protected function getClient();

    abstract protected function getConfiguration();

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
        $allProjects = $this->getAllProjects();

        $projects = new \ArrayObject();
        foreach ($allProjects as &$project) {
            if (!isset($project->projectCategory) || 'Lukket' !== $project->projectCategory['name']) {
                $project->url = parse_url($project->self, PHP_URL_SCHEME).'://'.parse_url($project->self, PHP_URL_HOST).'/browse/'.$project->key;
                $projects[] = $project;
            }
        }

        return $projects;
    }

    /**
     * Get all projects, including archived.
     *
     * @return \ArrayObject
     */
    public function getAllProjects()
    {
        return $this->projects()->getAllProjects();
    }

    private function projects()
    {
        return new ProjectService($this->getConfiguration());
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
