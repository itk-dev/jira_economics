<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ProjectBilling\Service;

use Billing\Service\BillingService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ProjectBillingService
{
    private $billingService;

    private $boundExternalMaterialId;
    private $boundInternalMaterialId;

    /**
     * ProjectBillingService constructor.
     *
     * @param $boundExternalMaterialId
     * @param $boundInternalMaterialId
     */
    public function __construct(
        BillingService $billingService,
        $boundExternalMaterialId,
        $boundInternalMaterialId
    ) {
        $this->billingService = $billingService;

        $this->boundExternalMaterialId = $boundExternalMaterialId;
        $this->boundInternalMaterialId = $boundInternalMaterialId;
    }

    public function getProjects()
    {
        return $this->billingService->getProjects();
    }

    /**
     * Create export data for the given tasks.
     *
     * @param array     $tasks                      array of Jira tasks
     * @param int       $supplier                   supplier number
     * @param string    $psp                        PSP string
     * @param int       $selectedProject            jira project ID
     * @param \DateTime $from                       invoice from time
     * @param \DateTime $to                         invoice to time
     * @param string    $description
     * @param bool      $includeProjectNameInHeader
     *
     * @return array
     */
    public function createExportData(array $tasks, int $supplier, string $psp, int $selectedProject, \DateTime $from, \DateTime $to, string $description, bool $includeProjectNameInHeader = false)
    {
        $entries = [];
        $accounts = $this->billingService->getAllAccounts();
        $accounts = array_reduce($accounts, function ($carry, $account) {
            $carry[$account->id] = $account;

            return $carry;
        }, []);

        $project = $this->billingService->getProject($selectedProject);

        $accountFieldId = $this->billingService->getCustomFieldId('Account');

        foreach ($tasks as $task) {
            $account = $task->fields->{$accountFieldId};
            $account = $accounts[$account->id];

            $internal = 'INTERN' === $account->category->name;

            if (isset($entries[$account->id])) {
                $header = $entries[$account->id]->header;
            } else {
                $header = $this->createHeaderForAccount($account, $supplier, $from, $to, $project, $description, $includeProjectNameInHeader);
            }

            // Get worklogs and expenses for task.
            $worklogs = $this->billingService->getIssueWorklogs($task->id);
            $expenses = $this->billingService->getExpenses([
                'scopeId' => $task->id,
                'scopeType' => 'ISSUE',
            ]);

            // Bail out if there is nothing to bill for task.
            if (0 === \count($worklogs) && 0 === \count($expenses)) {
                continue;
            }

            $lines = [];

            if (!isset($accounts[$account->id]->defaultPrice)) {
                $accounts[$account->id]->defaultPrice = $this->billingService->getAccountDefaultPrice($account->id);
            }

            // Create line data for worklogs.
            if (\count($worklogs) > 0) {
                $worklogsSum = array_reduce($worklogs, function ($carry, $item) {
                    $carry = $carry + $item->timeSpentSeconds;

                    return $carry;
                }, 0);

                // From seconds to hours.
                $worklogsSum = $worklogsSum / 60.0 / 60.0;

                $lines[] = (object) [
                    'materialNumber' => $internal ? $this->boundInternalMaterialId : $this->boundExternalMaterialId,
                    'product' => $task->key.': '.$task->fields->summary,
                    'amount' => $worklogsSum,
                    'price' => $accounts[$account->id]->defaultPrice,
                    'psp' => $psp,
                ];
            }

            // Create line data for expenses.
            if (\count($expenses) > 0) {
                $expensesSum = array_reduce($expenses, function ($carry, $item) {
                    $carry = $carry + $item->amount;

                    return $carry;
                }, 0);

                $lines[] = (object) [
                    'materialNumber' => $internal ? $this->boundInternalMaterialId : $this->boundExternalMaterialId,
                    'product' => $task->key.': '.$task->fields->summary,
                    'amount' => 1,
                    'price' => $expensesSum,
                    'psp' => $psp,
                ];
            }

            if (isset($entries[$account->id])) {
                $entries[$account->id]->lines = array_merge($entries[$account->id]->lines, $lines);
            } else {
                $entries[$account->id] = (object) [
                    'header' => $header,
                    'lines' => $lines,
                ];
            }
        }

        return $entries;
    }

    private function createHeaderForAccount($account, $supplier, \DateTime $from, \DateTime $to, $project, $description, $includeProjectNameInHeader)
    {
        $internal = 'INTERN' === $account->category->name;

        if ($internal) {
            return (object) [
                'debtor' => $account->customer->key,
                'salesChannel' => $account->category->key,
                'internal' => true,
                'contactName' => $account->contact->displayName,
                'description' => ($includeProjectNameInHeader ? $project->name.' - ' : '').$account->name.' ('.$from->format('d/m/Y').' - '.$to->format('d/m/Y').'). '.$description,
                'supplier' => $supplier,
            ];
        } else {
            return (object) [
                'debtor' => $account->customer->key,
                'salesChannel' => $account->category->key,
                'internal' => false,
                'contactName' => $account->contact->displayName,
                'description' => ($includeProjectNameInHeader ? $project->name.' - ' : '').$account->name.' ('.$from->format('d/m/Y').' - '.$to->format('d/m/Y').'). '.$description,
                'supplier' => $supplier,
                'ean' => $account->key,
                'periodFrom' => $from->format('d.m.Y'),
                'periodTo' => $to->format('d.m.Y'),
            ];
        }
    }

    /**
     * Mark the chosen issues a billed in Jira.
     *
     * @param array $issues array of Jira issues
     */
    public function markIssuesAsBilled(array $issues)
    {
        $billedCustomFieldId = $this->billingService->getCustomFieldId('Faktureret');

        foreach ($issues as $issue) {
            // Mark issue as billed.
            $this->billingService->put('/rest/api/2/issue/'.$issue->id, (object) [
                'fields' => [
                    $billedCustomFieldId => [
                        [
                            'value' => 'Faktureret',
                        ],
                    ],
                ],
            ]);

            // @TODO: Mark each worklog as billed in Jira?
            // @TODO: Mark each worklog and expense as billed in JiraEconomics?
        }
    }

    /**
     * Get all tasks in the interval from the project that have not been
     * billed and that have the status "Done".
     *
     * @param int            $projectId The Jira project id
     * @param \DateTime|null $from      start of interval
     * @param \DateTime|null $to        end of interval
     *
     * @return array
     */
    public function getAllNonBilledFinishedTasks(int $projectId, \DateTime $from = null, \DateTime $to = null)
    {
        $billedCustomFieldId = $this->billingService->getCustomFieldId('Faktureret');
        $accountFieldId = $this->billingService->getCustomFieldId('Account');

        $accounts = $this->billingService->getAllAccounts();
        $accounts = array_reduce($accounts, function ($carry, $account) {
            $carry[$account->id] = $account;

            return $carry;
        }, []);

        // Previously: 'status=done',
        $jqls = [
            'status=lukket'
        ];

        if (null !== $from) {
            $jqls = array_merge($jqls, [
                'resolutiondate>="'.$from->format('Y/m/d').'"',
            ]);
        }
        if (null !== $to) {
            $jqls = array_merge($jqls, [
                'resolutiondate<="'.$to->format('Y/m/d').'"',
            ]);
        }

        // @TODO: Replace with more precise call to Jira, to avoid a lot of issues that are not relevant and have to be filtered out afterwards.
        $issues = $this->billingService->getProjectIssues($projectId, null, $jqls);

        $notBilledIssues = [];

        foreach ($issues as $issue) {
            // Ignore already billed issues.
            if (isset($issue->fields->{$billedCustomFieldId}) && 'Faktureret' === $issue->fields->{$billedCustomFieldId}[0]->value) {
                continue;
            }

            // Check that an account has been set for the issue.
            if (!isset($issue->fields->{$accountFieldId}) || !isset($accounts[$issue->fields->{$accountFieldId}->id])) {
                continue;
            }

            $account = $accounts[$issue->fields->{$accountFieldId}->id];

            // Ignore issue if the account is not INTERN or EKSTERN.
            if (!\in_array($account->category->name, ['INTERN', 'EKSTERN'])) {
                continue;
            }

            $notBilledIssues[$issue->id] = $issue;
        }

        return $notBilledIssues;
    }

    /**
     * Export the selected tasks to a spreadsheet.
     *
     * @param array $entries Array of invoice entries of the form:
     *
     *                       (object) [
     *                       'header' => [
     *                       'debtor' => DEBTOR,
     *                       'salesChannel' => SALES_CHANNEL,
     *                       'internal' => true/false,
     *                       'contactName' => CONTACT_NAME,
     *                       'description' => DESCRIPTION,
     *                       'supplier' => SUPPLIER,
     *                       'ean' => EAN_FOR_EXTERNAL,
     *                       ],
     *                       'lines' => [
     *                       (object) [
     *                       'materialNumber' => MATERIAL_NUMBER',
     *                       'product' => PRODUCT,
     *                       'amount' => AMOUNT,
     *                       'price' => PRICE,
     *                       'psp' => 'PSP',
     *                       ],
     *                       ...
     *                       ],
     *                       ];
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function exportTasksToSpreadsheet(array $entries)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;

        $today = new \DateTime();
        $todayString = $today->format('d.m.Y');
        $todayPlus30days = $today->add(new \DateInterval('P30D'));

        // Move ahead if the day is a saturday or sunday to ensure it is a bank day.
        // TODO: Handle holidays.
        $weekday = $todayPlus30days->format('N');
        if ($weekday == '6') {
            $todayPlus30days->add(new \DateInterval('P2D'));
        } else if ($weekday == '7') {
            $todayPlus30days->add(new \DateInterval('P1D'));
        }

        foreach ($entries as $entry) {
            $header = $entry->header;

            // Generate header line (H).
            // A. "Linietype"
            $sheet->setCellValueByColumnAndRow(1, $row, 'H');
            // B. "Ordregiver/Bestiller"
            $sheet->setCellValueByColumnAndRow(2, $row, str_pad($header->debtor, 10, '0', \STR_PAD_LEFT));
            // D. "Fakturadato"
            $sheet->setCellValueByColumnAndRow(4, $row, $todayString);
            // E. "Bilagsdato"
            $sheet->setCellValueByColumnAndRow(5, $row, $todayString);
            // F. "Salgsorganisation"
            $sheet->setCellValueByColumnAndRow(6, $row, '0020');
            // G. "Salgskanal"
            $sheet->setCellValueByColumnAndRow(7, $row, $header->salesChannel);
            // H. "Division"
            $sheet->setCellValueByColumnAndRow(8, $row, '20');
            // I. "Ordreart"
            $sheet->setCellValueByColumnAndRow(9, $row, $header->internal ? 'ZIRA' : 'ZRA');
            // O. "Kunderef.ID"
            $sheet->setCellValueByColumnAndRow(15, $row, isset($header->contactName) ? substr('Att: '.$header->contactName, 0, 35) : '');
            // P. "Toptekst, yderligere spec i det hvide felt på fakturaen"
            $sheet->setCellValueByColumnAndRow(16, $row, substr($header->description, 0, 500));
            // Q. "Leverandør"
            if ($header->internal) {
                $sheet->setCellValueByColumnAndRow(17, $row, str_pad($header->supplier, 10, '0', \STR_PAD_LEFT));
            }
            // R. "EAN nr."
            if (!$header->internal && 13 === \strlen($header->ean)) {
                $sheet->setCellValueByColumnAndRow(18, $row, $header->ean);
            }

            // External invoices.
            if (!$header->internal) {
                // 38. Stiftelsesdato: dagsdato
                $sheet->setCellValueByColumnAndRow(38, $row, $todayString);
                // 39. Periode fra
                $sheet->setCellValueByColumnAndRow(39, $row, $header->periodFrom);
                // 40. Periode til
                $sheet->setCellValueByColumnAndRow(40, $row, $header->periodTo);
                // 46. Fordringstype oprettelse/valg : KOCIVIL
                $sheet->setCellValueByColumnAndRow(46, $row, 'KOCIVIL');
                // 49. Forfaldsdato: dagsdato
                $sheet->setCellValueByColumnAndRow(49, $row, $todayString);
                // 50. Henstand til: dagsdato + 30 dage. NB det må ikke være før faktura forfald. Skal være en bank dag.
                $sheet->setCellValueByColumnAndRow(50, $row, $todayPlus30days->format('d.m.Y'));
            }

            $lines = $entry->lines;

            ++$row;

            foreach ($lines as $line) {
                // Generate invoice lines (L).
                // A. "Linietype"
                $sheet->setCellValueByColumnAndRow(1, $row, 'L');
                // B. "Materiale (vare)nr.
                $sheet->setCellValueByColumnAndRow(2, $row, str_pad($line->materialNumber, 18, '0', \STR_PAD_LEFT));
                // C. "Beskrivelse"
                $sheet->setCellValueByColumnAndRow(3, $row, $line->product);
                // D. "Ordremængde"
                $sheet->setCellValueByColumnAndRow(4, $row, number_format($line->amount, 3, ',', ''));
                // E. "Beløb pr. enhed"
                $sheet->setCellValueByColumnAndRow(5, $row, number_format($line->price, 2, ',', ''));
                // F. "Priser fra SAP"
                $sheet->setCellValueByColumnAndRow(6, $row, 'NEJ');
                // G. "PSP-element nr."
                $sheet->setCellValueByColumnAndRow(7, $row, $line->psp);

                ++$row;
            }
        }

        return $spreadsheet;
    }
}
