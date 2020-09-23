<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace GraphicServiceBilling\Service;

use Billing\Service\BillingService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class GraphicServiceBillingService
{
    private $boundProjectId;
    private $billingService;
    private $boundReceiverAccount;
    private $boundReceiverPSPExpenses;
    private $boundReceiverPSPWorklogs;
    private $boundMaterialId;
    private $boundWorklogPricePerHour;

    /**
     * GraphicServiceBillingService constructor.
     *
     * @param $boundProjectId
     * @param \Billing\Service\BillingService $billingService
     * @param $boundReceiverAccount
     * @param $boundReceiverPSPWorklogs
     * @param $boundReceiverPSPExpenses
     * @param $boundMaterialId
     * @param $boundWorklogPricePerHour
     */
    public function __construct(
        $boundProjectId,
        BillingService $billingService,
        $boundReceiverAccount,
        $boundReceiverPSPWorklogs,
        $boundReceiverPSPExpenses,
        $boundMaterialId,
        $boundWorklogPricePerHour
    ) {
        $this->boundProjectId = $boundProjectId;
        $this->billingService = $billingService;
        $this->boundReceiverAccount = $boundReceiverAccount;
        $this->boundReceiverPSPExpenses = $boundReceiverPSPExpenses;
        $this->boundReceiverPSPWorklogs = $boundReceiverPSPWorklogs;
        $this->boundMaterialId = $boundMaterialId;
        $this->boundWorklogPricePerHour = $boundWorklogPricePerHour;
    }

    /**
     * Create export data for the given tasks.
     *
     * @param array $tasks array of Jira tasks
     *
     * @return array
     */
    public function createExportDataMarketing(array $tasks)
    {
        // Get debtor custom field.
        $debtorFieldId = $this->billingService->getCustomFieldId('Debitor');
        $marketingAccountFieldId = $this->billingService->getCustomFieldId('Marketing Account');
        $libraryFieldId = $this->billingService->getCustomFieldId('Library');

        $entries = [];

        foreach ($tasks as $task) {
            $marketingAccount = $task->fields->{$marketingAccountFieldId} ?? false;

            if ('Markedsføringskonto' !== $marketingAccount[0]->value) {
                continue;
            }

            $description = '';

            $library = $task->fields->{$libraryFieldId};

            if (isset($entries[$library])) {
                $header = $entries[$library]->header;
            } else {
                // Create header line data.
                $header = (object) [
                    'debtor' => $this->boundReceiverAccount,
                    'salesChannel' => '10',
                    'internal' => true,
                    'description' => $description,
                    'supplier' => $this->boundReceiverAccount,
                    'library' => $library,
                    'marketing' => $marketingAccount,
                ];
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

            $header->description = $header->description.(\strlen($header->description) > 0 ? ', ' : '').$task->fields->reporter->displayName.': '.$task->key;

            $lines = [];

            // Create line data for worklogs.
            if (\count($worklogs) > 0) {
                $worklogsSum = array_reduce($worklogs, function ($carry, $item) {
                    $carry = $carry + $item->timeSpentSeconds;

                    return $carry;
                }, 0);

                // From seconds to hours.
                $worklogsSum = $worklogsSum / 60.0 / 60.0;

                $lines[] = (object) [
                    'materialNumber' => $this->boundMaterialId,
                    'product' => 'Design '.$library,
                    'amount' => 1,
                    'price' => $worklogsSum * $this->boundWorklogPricePerHour,
                    'psp' => $this->boundReceiverPSPWorklogs,
                ];
            }

            // Create line data for expenses.
            if (\count($expenses) > 0) {
                $expensesSum = array_reduce($expenses, function ($carry, $item) {
                    $carry = $carry + $item->amount;

                    return $carry;
                }, 0);

                $lines[] = (object) [
                    'materialNumber' => $this->boundMaterialId,
                    'product' => 'Tryk '.$library,
                    'amount' => 1,
                    'price' => $expensesSum,
                    'psp' => $this->boundReceiverPSPExpenses,
                ];
            }

            if (isset($entries[$library])) {
                $entries[$library]->lines = array_merge($entries[$library]->lines, $lines);
            } else {
                $entries[$library] = (object) [
                    'header' => $header,
                    'lines' => $lines,
                ];
            }
        }

        return $entries;
    }

    /**
     * Create export data for the given tasks.
     *
     * @param array $tasks array of Jira tasks
     *
     * @return array
     *
     * @throws \Exception
     */
    public function createExportDataNotMarketing(array $tasks)
    {
        // Get debtor custom field.
        $debtorFieldId = $this->billingService->getCustomFieldId('Debitor');
        $entries = [];

        foreach ($tasks as $task) {
            $description = $this->getTaskDescription($task);
            $debtor = $task->fields->{$debtorFieldId} ?? false;

            // If no debtor has been set, ignore the task, but display warning.
            if (!$debtor) {
                throw new \Exception($task->key.': Debtor not set.', 404);
            }

            // Remove surrounding whitespace.
            $debtor = trim($debtor);

            // If debtor is not numeric, ignore the task, but display warning.
            if (!is_numeric($debtor)) {
                throw new \Exception($task->key.': Debtor is not a number.', 400);
            }

            // Create header line data.
            $header = (object) [
                'debtor' => $debtor,
                'salesChannel' => '10',
                'internal' => true,
                'contactName' => $task->fields->reporter->displayName,
                'description' => $description,
                'supplier' => $this->boundReceiverAccount,
            ];

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

            // Create line data for worklogs.
            if (\count($worklogs) > 0) {
                $worklogsSum = array_reduce($worklogs, function ($carry, $item) {
                    $carry = $carry + $item->timeSpentSeconds;

                    return $carry;
                }, 0);

                // From seconds to hours.
                $worklogsSum = $worklogsSum / 60.0 / 60.0;

                $lines[] = (object) [
                    'materialNumber' => $this->boundMaterialId,
                    'product' => 'Design '.$task->fields->summary,
                    'amount' => 1,
                    'price' => $worklogsSum * $this->boundWorklogPricePerHour,
                    'psp' => $this->boundReceiverPSPWorklogs,
                ];
            }

            // Create line data for expenses.
            if (\count($expenses) > 0) {
                $expensesSum = array_reduce($expenses, function ($carry, $item) {
                    $carry = $carry + $item->amount;

                    return $carry;
                }, 0);

                $lines[] = (object) [
                    'materialNumber' => $this->boundMaterialId,
                    'product' => 'Tryk '.$task->fields->summary,
                    'amount' => 1,
                    'price' => $expensesSum,
                    'psp' => $this->boundReceiverPSPExpenses,
                ];
            }

            $entries[] = (object) [
                'header' => $header,
                'lines' => $lines,
            ];
        }

        return $entries;
    }

    /**
     * Get task description.
     *
     * @param \stdClass $task the Jira task
     *
     * @return string
     */
    private function getTaskDescription(\stdClass $task)
    {
        $orderLinesFieldId = $this->billingService->getCustomFieldId('Order lines');

        // Get order lines from jira task.
        $orderLines = $task->fields->{$orderLinesFieldId} ?? '';

        return trim(implode('', [
            $task->key.': ',
            $task->fields->summary,
            !empty($orderLines) ? '. Ordrelinjer: ' : '',
            // Replace \\ in orderLines string field with .
            !empty($orderLines) ? preg_replace('/\\\\\\\\/', '. ', $orderLines) : '',
        ]));
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
            $this->billingService->put('/rest/api/2/issue/'.$issue->id, (object) [
                'fields' => [
                    $billedCustomFieldId => [
                        [
                            'value' => 'Faktureret',
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * Get all tasks in the interval from the project that have not been
     * billed and that have the status "Done".
     *
     * @param int            $projectId the Jira project id
     * @param \DateTime|null $fromDate
     * @param \DateTime|null $toDate
     * @param bool           $marketing
     *
     * @return array
     */
    public function getAllNonBilledFinishedTasks(int $projectId, \DateTime $fromDate = null, \DateTime $toDate = null, bool $marketing = false)
    {
        $billedCustomFieldId = $this->billingService->getCustomFieldId('Faktureret');
        $marketingAccountCustomFieldId = $this->billingService->getCustomFieldId('Marketing Account');

        $notBilledIssues = [];

        $issues = $this->billingService->getProjectIssues($projectId);

        foreach ($issues as $issue) {
            // Ignore issues that are not Done.
            if ('done' !== $issue->fields->status->statusCategory->key) {
                continue;
            }

            // Ignore already billed issues.
            if (isset($issue->fields->{$billedCustomFieldId}) && 'Faktureret' === $issue->fields->{$billedCustomFieldId}[0]->value) {
                continue;
            }

            // Ignore issues that are not resolved within the selected period.
            try {
                $resolutionDate = new \DateTime($issue->fields->resolutiondate);
            } catch (\Exception $e) {
                // If resolution does not exist, ignore the issue.
                continue;
            }

            // Ignore issues that are resolved before fromDate, if set.
            if (null !== $fromDate && $resolutionDate < $fromDate) {
                continue;
            }

            // Ignore issues that are resolved after toDate, if set.
            if (null !== $toDate && $resolutionDate > $toDate) {
                continue;
            }

            // Select issues that are from the marketing account or not (not both).
            if (isset($issue->fields->{$marketingAccountCustomFieldId})) {
                $marketingField = $issue->fields->{$marketingAccountCustomFieldId}[0];
                if (!$marketing && 'Markedsføringskonto' === $marketingField->value) {
                    continue;
                }
            } else {
                if ($marketing) {
                    continue;
                }
            }

            $notBilledIssues[$issue->id] = $issue;
        }

        return $notBilledIssues;
    }

    /**
     * Export the selected tasks to a spreadsheet.
     *
     * @param array $invoiceEntries array of invoice entries
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function exportTasksToSpreadsheet(array $invoiceEntries)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;

        foreach ($invoiceEntries as $entry) {
            $header = $entry->header;

            // Replace all special characters with spaces.
            $description = $this->sanitizeInput($header->description ?? '');

            // Generate header line (H).
            // A. "Linietype"
            $sheet->setCellValueByColumnAndRow(1, $row, 'H');
            // B. "Ordregiver/Bestiller"
            $sheet->setCellValueByColumnAndRow(2, $row, str_pad($header->debtor, 10, '0', STR_PAD_LEFT));
            // D. "Fakturadato"
            $sheet->setCellValueByColumnAndRow(4, $row, (new \DateTime())->format('d.m.Y'));
            // E. "Bilagsdato"
            $sheet->setCellValueByColumnAndRow(5, $row, (new \DateTime())->format('d.m.Y'));
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
            $sheet->setCellValueByColumnAndRow(16, $row, substr($description, 0, 500));
            // Q. "Leverandør"
            if ($header->internal) {
                $sheet->setCellValueByColumnAndRow(17, $row, str_pad($header->supplier, 10, '0', STR_PAD_LEFT));
            }

            $lines = $entry->lines;

            ++$row;

            foreach ($lines as $line) {
                // Replace all special characters with spaces.
                $product = $this->sanitizeInput($line->product ?? '');

                // Generate invoice lines (L).
                // A. "Linietype"
                $sheet->setCellValueByColumnAndRow(1, $row, 'L');
                // B. "Materiale (vare)nr.
                $sheet->setCellValueByColumnAndRow(2, $row, str_pad($line->materialNumber, 18, '0', STR_PAD_LEFT));
                // C. "Beskrivelse"
                $sheet->setCellValueByColumnAndRow(3, $row, $product);
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

    /**
     * Replace all unwanted characters from the input with spaces.
     *
     * @param string $input
     *
     * @return string|string[]|null
     */
    private function sanitizeInput(string $input) {
        return preg_replace('/[^A-Za-z0-9æÆøØåÅ]/', ' ', $input);
    }
}
