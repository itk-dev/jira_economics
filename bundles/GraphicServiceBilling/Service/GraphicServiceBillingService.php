<?php

namespace GraphicServiceBilling\Service;

use Billing\Service\BillingService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class GraphicServiceBillingService
{
    private $boundProjectId;
    private $billingService;
    private $boundReceiverAccount;
    private $boundReceiverPSP;
    private $boundMaterialId;

    /**
     * GraphicServiceBillingService constructor.
     * @param $boundProjectId
     * @param \Billing\Service\BillingService $billingService
     * @param $boundReceiverAccount
     * @param $boundReceiverPSP
     * @param $boundMaterialId
     */
    public function __construct(
        $boundProjectId,
        BillingService $billingService,
        $boundReceiverAccount,
        $boundReceiverPSP,
        $boundMaterialId
    ) {
        $this->boundProjectId = $boundProjectId;
        $this->billingService = $billingService;
        $this->boundReceiverAccount = $boundReceiverAccount;
        $this->boundReceiverPSP = $boundReceiverPSP;
        $this->boundMaterialId = $boundMaterialId;
    }

    /**
     * Create export data for the given interval.
     *
     * @param \DateTime $from Start of interval.
     * @param \DateTime $to End of interval.
     * @param bool $marketing Marketing account or not.
     * @return array
     */
    public function createExportData(\DateTime $from, \DateTime $to, bool $marketing)
    {
        // Get all tasks in the interval from the project that have not been
        // billed and that have the status "Order completed and sent".
        $tasks = $this->getAllNonBilledFinishedTasks($this->boundProjectId, $from, $to, $marketing);

        // Get debtor custom field.
        $debtorCustomFieldId = $this->billingService->getCustomFieldId('Debitor');

        $entries = [];

        foreach ($tasks as $task) {
            $description1 = $task->fields->description;

            $description = preg_replace('/\\\\ \[Åbn filer i OwnCloud.*]\\ /i', '', $description1);
            $description = preg_replace('/\\\\/', '', $description);

            $description = implode([
                $task->fields->summary,
                ' (' .  $task->key . '): ',
                $description,
            ]);

            // Create header line data.
            $header = (object) [
                'debtor' => isset($task->fields->{$debtorCustomFieldId}) ? $task->fields->{$debtorCustomFieldId} : '',
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

            // Bail out if there are nothing to bill for task.
            if (count($worklogs) == 0 && count($expenses) == 0) {
                // @TODO: Should task be marked as billed?

                continue;
            }

            $lines = [];

            // Create line data for worklogs.
            if (count($worklogs) > 0) {
                $worklogsSum = array_reduce($worklogs, function ($carry, $item) {
                    $carry = $carry + $item->timeSpentSeconds;
                    return $carry;
                }, 0);

                $lines[] = (object) [
                    'materialNumber' => $this->boundMaterialId,
                    'product' => 'Design ' . $task->fields->summary,
                    'amount' => 1,
                    // @TODO: What is the price per hour?
                    'price' => $worklogsSum,
                    'psp' => $this->boundReceiverPSP,
                ];
            }

            // Create line data for expenses.
            if (count($expenses) > 0) {
                $expensesSum = array_reduce($expenses, function ($carry, $item) {
                    $carry = $carry + $item->amount;
                    return $carry;
                }, 0);

                $lines[] = (object) [
                    'materialNumber' => $this->boundMaterialId,
                    'product' => 'Tryk ' . $task->fields->summary,
                    'amount' => 1,
                    'price' => $expensesSum,
                    'psp' => $this->boundReceiverPSP,
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
     * Get all tasks in the interval from the project that have not been
     * billed and that have the status "Order completed and sent".
     *
     * @param $projectId
     * @param \DateTime|null $from
     * @param \DateTime|null $to
     * @param bool $marketing
     * @return array
     */
    private function getAllNonBilledFinishedTasks($projectId, \DateTime $from = null, \DateTime $to = null, bool $marketing = false)
    {
        $billedCustomFieldId = $this->billingService->getCustomFieldId('Faktureret');

        $unbilledIssues = [];

        $issues = $this->billingService->getProjectIssues($projectId);

        foreach ($issues as $issue) {
            if ($issue->fields->status->statusCategory->key != 'done') {
                continue;
            }

            // Ignore already billed issues.
            if (isset($issue->fields->{$billedCustomFieldId}) && $issue->fields->{$billedCustomFieldId}->value == 'Faktureret') {
                continue;
            }

            // @TODO: Resolution date between $from and $to.

            // @TODO: Only from/not_from marketing account.

            $unbilledIssues[$issue->id] = $issue;
        }

        return $unbilledIssues;
    }

    /**
     * Export the selected invoices (by id) to csv.
     *
     * @param array $entries
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function exportInvoicesToSpreadsheet(array $entries)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;

        foreach ($entries as $entry) {
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
            $sheet->setCellValueByColumnAndRow(15, $row, substr('Att: '.$header->contactName, 0, 35));
            // P. "Toptekst, yderligere spec i det hvide felt på fakturaen"
            $sheet->setCellValueByColumnAndRow(16, $row, substr($header->description, 0, 500));
            // Q. "Leverandør"
            if ($header->internal) {
                $sheet->setCellValueByColumnAndRow(17, $row, str_pad($header->supplier, 10, '0', STR_PAD_LEFT));
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
