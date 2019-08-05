<?php

namespace Planning\Service;

use App\Service\JiraService;

class PlanningService extends JiraService
{
    /**
     * Get all future sprints.
     *
     * @return array
     */
    public function getFutureSprints() {
        $boardId = getenv('JIRA_DEFAULT_BOARD');
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
     * @param $sprintId
     * @return array
     */
    public function getIssuesInSprint($sprintId) {
        $boardId = getenv('JIRA_DEFAULT_BOARD');
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
