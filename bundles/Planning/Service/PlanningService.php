<?php

namespace Planning\Service;

use App\Service\JiraService;

class PlanningService extends JiraService
{
    /**
     * Constructor.
     */
    public function __construct(
        $jiraUrl,
        $tokenStorage,
        $customerKey,
        $pemPath
    ) {
        parent::__construct($jiraUrl, $tokenStorage, $customerKey, $pemPath);
    }

    public function getBoard($boardId)
    {
        return $this->get('/rest/agile/1.0/board/'.$boardId);
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
            $result = $this->get('/rest/agile/1.0/board?maxResults=50&startAt=' . $start);
            $boards = array_merge($boards, $result->values);

            if ($result->isLast) {
                break;
            }

            $start = $start + 50;
        }

        return $boards;
    }

    /**
     * Get all future sprints.
     *
     * @param $boardId
     * @return array
     */
    public function getFutureSprints($boardId) {
        $sprints = [];

        $start = 0;
        while (true) {
            $result = $this->get('/rest/agile/1.0/board/' . $boardId . '/sprint?startAt='.$start.'&maxResults=50&state=future,active');
            $sprints = array_merge($sprints, $result->values);

            if ($result->isLast) {
                break;
            }

            $start = $start + 50;
        }

        return $sprints;
    }

    /**
     * Get all issues for sprint.
     *
     * @param $boardId
     * @param $sprintId
     * @return array
     */
    public function getIssuesInSprint($boardId, $sprintId) {
        $issues = [];
        $fields = implode(
            ',',
            [
                'timetracking',
                'summary',
                'status',
                'assignee',
                'project',
            ]
        );

        $start = 0;
        while (true) {
            $result = $this->get('/rest/agile/1.0/board/'.$boardId.'/sprint/'.$sprintId.'/issue?startAt=' . $start . '&fields='.$fields);
            $issues = array_merge($issues, $result->issues);

            $start = $start + 50;

            if ($start > $result->total) {
                break;
            }
        }

        return $issues;
    }
}