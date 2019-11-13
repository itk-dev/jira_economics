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
    private $boundProjectId;
    private $billingService;
    private $boundReceiverAccount;
    private $boundReceiverPSP;
    private $boundMaterialId;
    private $boundWorklogPricePerHour;

    /**
     * ProjectBillingService constructor.
     *
     * @param $boundProjectId
     * @param \Billing\Service\BillingService $billingService
     * @param $boundReceiverAccount
     * @param $boundReceiverPSP
     * @param $boundMaterialId
     * @param $boundWorklogPricePerHour
     */
    public function __construct(
        $boundProjectId,
        BillingService $billingService,
        $boundReceiverAccount,
        $boundReceiverPSP,
        $boundMaterialId,
        $boundWorklogPricePerHour
    ) {
        $this->boundProjectId = $boundProjectId;
        $this->billingService = $billingService;
        $this->boundReceiverAccount = $boundReceiverAccount;
        $this->boundReceiverPSP = $boundReceiverPSP;
        $this->boundMaterialId = $boundMaterialId;
        $this->boundWorklogPricePerHour = $boundWorklogPricePerHour;
    }

    public function getProjects()
    {
        return $this->billingService->getProjects();
    }

    /**
     * Create export data for the given tasks.
     *
     * @param array $tasks array of Jira tasks
     *
     * @return array
     */
    public function createExportData(array $tasks)
    {
        $entries = [];
        $accounts = $this->billingService->getAllAccounts();
        $accounts = array_reduce($accounts, function ($carry, $account) {
            $carry[$account->id] = $account;
            return $carry;
        }, []);

        $accountFieldId = $this->billingService->getCustomFieldId('Account');

        foreach ($tasks as $task) {
            $account = $task->fields->{$accountFieldId};
            $account = $accounts[$account->id];

            $internal = $account->category->name == 'INTERN';

            if (isset($entries[$account->id])) {
                $header = $entries[$account->id]->header;
            } else {
                $header = $this->createHeaderForAccount($account);
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

            // Update description.
            $header->description = $header->description.(\strlen($header->description) > 0 ? ', ' : '').$task->key;

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
                    'materialNumber' => $internal ? 103361 : 100006,
                    'product' => $task->fields->summary,
                    'amount' => $worklogsSum,
                    'price' => $accounts[$account->id]->defaultPrice,
                    'psp' => $this->boundReceiverPSP,
                ];
            }

            // Create line data for expenses.
            if (\count($expenses) > 0) {
                $expensesSum = array_reduce($expenses, function ($carry, $item) {
                    $carry = $carry + $item->amount;

                    return $carry;
                }, 0);

                $lines[] = (object) [
                    'materialNumber' => $internal ? 103361 : 100006,
                    'product' => $task->fields->summary,
                    'amount' => 1,
                    'price' => $expensesSum,
                    'psp' => $this->boundReceiverPSP,
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

    private function createHeaderForAccount($account) {
        $internal = $account->category->name == 'INTERN';

        if ($internal) {
            return (object) [
                'debtor' => $account->customer->key,
                'salesChannel' => $account->category->key,
                'internal' => true,
                'contactName' => $account->contact->displayName,
                'description' => '',
                'supplier' => $this->boundReceiverAccount,
            ];
        }
        else {
            return (object) [
                'debtor' => $account->customer->key,
                'salesChannel' => $account->category->key,
                'internal' => false,
                'contactName' => $account->contact->displayName,
                'description' => '',
                'supplier' => $this->boundReceiverAccount,
                'ean' => $account->key,
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
     * @param int $projectId The Jira project id
     *
     * @param \DateTime|null $from Start of interval.
     * @param \DateTime|null $to End of interval.
     * @return array
     */
    public function getAllNonBilledFinishedTasks(int $projectId, \DateTime $from = null, \DateTime $to = null)
    {
        $billedCustomFieldId = $this->billingService->getCustomFieldId('Faktureret');

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
            if (null !== $from) {
                $diffFrom = $resolutionDate->diff($from)->format('%R');
                if ('+' === $diffFrom) {
                    continue;
                }
            }
            if (null !== $to) {
                $diffTo = $resolutionDate->diff($to)->format('%R');
                if ('-' === $diffTo) {
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
            $sheet->setCellValueByColumnAndRow(16, $row, substr($header->description, 0, 500));
            // Q. "Leverandør"
            if ($header->internal) {
                $sheet->setCellValueByColumnAndRow(17, $row, str_pad($header->supplier, 10, '0', STR_PAD_LEFT));
            }
            // R. "EAN nr."
            if (!$header->internal && 13 === \strlen($header->ean)) {
                $sheet->setCellValueByColumnAndRow(18, $row, $header->ean);
            }

            $lines = $entry->lines;

            ++$row;

            foreach ($lines as $line) {
                // Generate invoice lines (L).
                // A. "Linietype"
                $sheet->setCellValueByColumnAndRow(1, $row, 'L');
                // B. "Materiale (vare)nr.
                $sheet->setCellValueByColumnAndRow(2, $row, str_pad($line->materialNumber, 18, '0', STR_PAD_LEFT));
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
