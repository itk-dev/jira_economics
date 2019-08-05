<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use Expense\Entity\Category as ExpenseCategory;
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
    abstract protected function getClient();

    /**
     * Get from Jira.
     *
     * @param $path
     *
     * @return mixed
     */
    public function get($path)
    {
        $client = $this->getClient();

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
        $client = $this->getClient();

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
        $client = $this->getClient();

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
     * Delete in Jira.
     *
     * @param $path
     *
     * @return mixed
     */
    protected function delete($path)
    {
        $client = $this->getClient();

        // Set the "auth" request option to "oauth" to sign using oauth
        try {
            $response = $client->delete($path);

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

    /**
     * @see http://developer.tempo.io/doc/core/api/rest/latest/#1349331745
     */
    public function getExpenseCategories()
    {
        $result = $this->get('/rest/tempo-core/1/expense/category/');

        return $result;
    }

    public function getExpenseCategory(int $id)
    {
        $categories = $this->getExpenseCategories();

        foreach ($categories as $category) {
            if ($id === $category->id) {
                return $category;
            }
        }

        return null;
    }

    public function getExpenseCategoryByName(string $name)
    {
        $categories = $this->getExpenseCategories();

        foreach ($categories as $category) {
            if ($name === $category->name) {
                return $category;
            }
        }

        return null;
    }

    public function createExpenseCategory(ExpenseCategory $category)
    {
        $result = $this->post('/rest/tempo-core/1/expense/category/', [
            'name' => $category->getName(),
        ]);

        return $result;
    }

    public function updateExpenseCategory(ExpenseCategory $category)
    {
        $result = $this->put('/rest/tempo-core/1/expense/category/'.$category->getId().'/', [
            'name' => $category->getName(),
        ]);

        return $result;
    }

    public function deleteExpenseCategory(ExpenseCategory $category)
    {
        $result = $this->delete('/rest/tempo-core/1/expense/category/'.$category->getId().'/');

        return $result;
    }
}
