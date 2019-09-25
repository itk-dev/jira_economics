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
use Expense\Entity\Category;
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
    public function get($path, array $query = [])
    {
        $client = $this->getClient();

        try {
            $response = $client->get($path, ['query' => $query]);

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
                $result->url = parse_url(
                    $result->self,
                    PHP_URL_SCHEME
                ).'://'.parse_url(
                    $result->self,
                    PHP_URL_HOST
                ).'/browse/'.$result->key;
                $projects[] = $result;
            }
        }

        return $projects;
    }

    /**
     * Get all boards.
     *
     * @return array
     */
    public function getAllBoards()
    {
        $boards = [];

        $start = 0;
        while (true) {
            $result = $this->get('/rest/agile/1.0/board?maxResults=50&startAt='.$start);
            $boards = array_merge($boards, $result->values);

            if ($result->isLast) {
                break;
            }

            $start = $start + 50;
        }

        return $boards;
    }

    /**
     * Get board by id.
     *
     * @param $boardId
     *
     * @return mixed
     */
    public function getBoard($boardId)
    {
        return $this->get('/rest/agile/1.0/board/'.$boardId);
    }

    /**
     * Get all worklogs for project.
     *
     * @param $projectId
     * @param string $from
     * @param string $to
     *
     * @return mixed
     */
    public function getProjectWorklogs($projectId, $from = '2000-01-01', $to = '3000-01-01')
    {
        $worklogs = $this->post('rest/tempo-timesheets/4/worklogs/search', [
            'from' => $from,
            'to' => $to,
            'projectId' => [$projectId],
        ]);

        return $worklogs;
    }

    /**
     * Get all worklogs for issue.
     *
     * @param $issueId
     * @param string $from
     * @param string $to
     *
     * @return array
     */
    public function getIssueWorklogs($issueId, $from = '2000-01-01', $to = '3000-01-01')
    {
        $worklogs = $this->post('rest/tempo-timesheets/4/worklogs/search', [
            'from' => $from,
            'to' => $to,
            'taskId' => [$issueId],
        ]);

        return $worklogs;
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

    public function getAccount($accountId)
    {
        return $this->get('/rest/tempo-accounts/1/account/'.$accountId.'/');
    }

    public function getRateTableByAccount($accountId)
    {
        return $this->get('/rest/tempo-accounts/1/ratetable', [
            'scopeId' => $accountId,
            'scopeType' => 'ACCOUNT',
        ]);
    }

    public function getAccountDefaultPrice($accountId)
    {
        $rateTable = $this->getRateTableByAccount($accountId);

        foreach ($rateTable->rates as $rate) {
            if ('DEFAULT_RATE' === $rate->link->type) {
                return $rate->amount;
            }
        }

        return null;
    }

    public function getAccountIdsByProject($projectId)
    {
        $projectLinks = $this->get('/rest/tempo-accounts/1/link/project/'.$projectId);

        return array_reduce($projectLinks, function ($carry, $item) {
            $carry[] = $item->accountId;

            return $carry;
        }, []);
    }

    /**
     * Get tempo custom fields.
     *
     * @return mixed
     */
    public function getTempoCustomFields()
    {
        $customFields = $this->get('/rest/tempo-accounts/1/field/');

        return $customFields;
    }

    /**
     * Get all tempo categories.
     *
     * @return mixed
     */
    public function getTempoCategories()
    {
        $customFields = $this->get('/rest/tempo-accounts/1/category/');

        return $customFields;
    }

    /**
     * Get customer by id.
     *
     * @param $id
     *
     * @return mixed
     */
    public function getTempoCustomer($id)
    {
        return $this->get('/rest/tempo-accounts/1/customer/'.$id);
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
     * Get users from search.
     *
     * @return mixed
     */
    public function searchUser($username)
    {
        $result = $this->get('/rest/api/2/user/search', ['username' => $username]);

        return $result;
    }

    /**
     * Create a new jira user.
     *
     * @return mixed
     */
    public function createUser($user)
    {
        $result = $this->post('/rest/api/2/user', $user);

        return $result;
    }

    public function search(array $query)
    {
        $result = $this->get('/rest/api/2/search', $query);

        return $result;
    }

    public function getIssueUrl($issue)
    {
        $key = $issue->key ?? $issue;

        return $this->jiraUrl.'/browse/'.$key;
    }

    /**
     * @see https://docs.atlassian.com/software/jira/docs/api/REST/8.3.1/?_ga=2.202569298.2139473575.1564917078-393255252.1550779361#api/2/issue-getIssuePickerResource
     *
     * @param string $project
     * @param string $query
     *
     * @return mixed
     */
    public function issuePicker(string $project, string $query)
    {
        $result = $this->get('/rest/api/2/issue/picker', [
            'currentJQL' => 'project="'.$project.'"',
            'query' => $query,
        ]);

        return $result;
    }

    public function getIssue($issueIdOrKey)
    {
        $result = $this->get('/rest/api/2/issue/'.$issueIdOrKey);

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
        $result = $this->put(
            '/rest/tempo-core/1/expense/category/'.$category->getId().'/',
            [
                'name' => $category->getName(),
            ]
        );

        return $result;
    }

    public function deleteExpenseCategory(ExpenseCategory $category)
    {
        $result = $this->delete('/rest/tempo-core/1/expense/category/'.$category->getId().'/');

        return $result;
    }

    /**
     * @see http://developer.tempo.io/doc/core/api/rest/latest/#1349331745
     */
    public function getExpenses(array $query = [])
    {
        $result = $this->get('/rest/tempo-core/1/expense/', $query);

        return $result;
    }

    public function createExpense(array $data)
    {
        $category = $data['category'] ?? null;
        if (!$category instanceof Category) {
            throw new \RuntimeException('Invalid or missing category');
        }
        $data = [
            'expenseCategory' => [
                'id' => $category->getId(),
            ],
            'scope' => [
                'scopeType' => $data['scope_type'],
                'scopeId' => $data['scope_id'],
            ],
            'amount' => (int) ($data['quantity'] * $category->getUnitPrice()),
            'description' => $data['description'],
            'date' => (new \DateTime())->format(\DateTime::ATOM),
        ];
        $result = $this->post('/rest/tempo-core/1/expense/', $data);

        return $result;
    }
}
