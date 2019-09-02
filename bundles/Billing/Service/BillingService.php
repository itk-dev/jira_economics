<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace Billing\Service;

use App\Service\JiraService;
use Billing\Entity\Invoice;
use Billing\Entity\InvoiceEntry;
use Billing\Entity\Project;
use Billing\Entity\Worklog;
use Billing\Entity\Expense;
use Billing\Repository\ExpenseRepository;
use Billing\Repository\WorklogRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\EntityManagerInterface;

class BillingService extends JiraService
{
    private $entityManager;
    private $worklogRepository;
    private $expenseRepository;

    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param $jiraUrl
     * @param $tokenStorage
     * @param $customerKey
     * @param $pemPath
     * @param \Billing\Repository\WorklogRepository $worklogRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        $jiraUrl,
        $tokenStorage,
        $customerKey,
        $pemPath,
        WorklogRepository $worklogRepository,
        ExpenseRepository $expenseRepository
    ) {
        parent::__construct($jiraUrl, $tokenStorage, $customerKey, $pemPath);

        $this->entityManager = $entityManager;
        $this->worklogRepository = $worklogRepository;
        $this->expenseRepository = $expenseRepository;
    }

    /**
     * Get invoices for specific Jira project.
     *
     * @param $jiraProjectId
     *
     * @return array
     */
    public function getInvoices($jiraProjectId)
    {
        if (!(int) $jiraProjectId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $jiraProjectId]);

        if (!$project) {
            throw new HttpException(404, 'Project with id '.$jiraProjectId.' not found');
        }

        $invoices = [];

        foreach ($project->getInvoices() as $invoice) {
            $invoices[] = $this->getInvoiceArray($invoice);
        }

        return $invoices;
    }

    /**
     * Get all invoices.
     *
     * @return array
     */
    public function getAllInvoices()
    {
        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoices = $repository->findAll();

        if (!$invoices) {
            return [];
        }

        $invoicesArray = [];

        foreach ($invoices as $invoice) {
            $invoicesArray[] = $this->getInvoiceArray($invoice);
        }

        return $invoicesArray;
    }

    /**
     * Get specific invoice by id.
     *
     * @param $invoiceId
     *
     * @return array
     */
    public function getInvoice($invoiceId)
    {
        if (!(int) $invoiceId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceId]);

        if (!$invoice) {
            throw new HttpException(404, 'Invoice with id '.$invoiceId.' not found');
        }

        return $this->getInvoiceArray($invoice);
    }

    /**
     * Get invoice as array.
     *
     * @param \Billing\Entity\Invoice $invoice
     *
     * @return array
     */
    private function getInvoiceArray(Invoice $invoice)
    {
        // Get account information.
        $account = $this->getAccount($invoice->getAccountId());
        $account->defaultPrice = $this->getAccountDefaultPrice($invoice->getAccountId());

        $totalPrice = array_reduce($invoice->getInvoiceEntries()->toArray(), function ($carry, InvoiceEntry $entry) {
            return $carry + $entry->getAmount() * $entry->getPrice();
        }, 0);

        return [
            'id' => $invoice->getId(),
            'name' => $invoice->getName(),
            'projectId' => $invoice->getProject()->getJiraId(),
            'projectName' => $invoice->getProject()->getName(),
            'jiraId' => $invoice->getProject()->getJiraId(),
            'recorded' => $invoice->getRecorded(),
            'accountId' => $invoice->getAccountId(),
            'description' => $invoice->getDescription(),
            'account' => $account,
            'totalPrice' => $totalPrice,
            'created' => $invoice->getCreated()->format('c'),
        ];
    }

    /**
     * Post new invoice, creating a new entity referenced by the returned id.
     *
     * @param $invoiceData
     *
     * @return array invoiceData
     *
     * @throws \Exception
     */
    public function postInvoice($invoiceData)
    {
        if (empty($invoiceData['projectId']) || !(int) ($invoiceData['projectId'])) {
            throw new HttpException(400, "Expected integer value for 'projectId' in request");
        }

        if (empty($invoiceData['name'])) {
            throw new HttpException(400, "Expected 'name' in request");
        }

        $repository = $this->entityManager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $invoiceData['projectId']]);

        // If project is not present in db, add it from Jira.
        if (!$project) {
            $project = $this->getJiraProject($invoiceData['projectId']);
        }

        $invoice = new Invoice();
        $invoice->setName($invoiceData['name']);
        $invoice->setProject($project);
        $invoice->setRecorded(false);
        $invoice->setCreated(new \DateTime('now'));
        $invoice->setAccountId($invoiceData['accountId']);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $this->getInvoiceArray($invoice);
    }

    /**
     * Put specific invoice, replacing the invoice referenced by the given id.
     *
     * @param $invoiceData
     *
     * @return array
     */
    public function putInvoice($invoiceData)
    {
        if (empty($invoiceData['id']) || !(int) ($invoiceData['id'])) {
            throw new HttpException(400, "Expected integer value for 'id' in request");
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceData['id']]);

        if (!$invoice) {
            throw new HttpException(404, 'Unable to update invoice with id '.$invoiceData['id'].' as it does not already exist');
        }

        if (!empty($invoiceData['name'])) {
            $invoice->setName($invoiceData['name']);
        }

        if (!empty($invoiceData['description'])) {
            $invoice->setDescription($invoiceData['description']);
        }

        if (!empty($invoiceData['accountId'])) {
            $invoice->setAccountId($invoiceData['accountId']);
        }

        if (isset($invoiceData['recorded'])) {
            $invoiceRecorded = $invoiceData['recorded'];
            $invoice->setRecorded($invoiceRecorded);
        }

        $this->entityManager->flush();

        return $this->getInvoiceArray($invoice);
    }

    /**
     * Delete specific invoice referenced by the given id.
     *
     * @param $invoiceId
     */
    public function deleteInvoice($invoiceId)
    {
        if (empty($invoiceId) || !(int) $invoiceId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceId]);

        if (!$invoice) {
            throw new HttpException(404, 'Invoice with id '.$invoiceId.' did not exist');
        }

        $this->entityManager->remove($invoice);
        $this->entityManager->flush();
    }

    /**
     * Get invoiceEntries for specific invoice.
     *
     * @param $invoiceId
     *
     * @return array
     */
    public function getInvoiceEntries($invoiceId)
    {
        if (!(int) $invoiceId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceId]);

        $invoiceEntries = $invoice->getInvoiceEntries();

        $invoiceEntriesJson = [];

        foreach ($invoiceEntries as $invoiceEntry) {
            $invoiceEntry = $this->getInvoiceEntryArray($invoiceEntry);

            $invoiceEntriesJson[] = $invoiceEntry;
        }

        return $invoiceEntriesJson;
    }

    /**
     * Get all invoiceEntries.
     *
     * @return array
     */
    public function getAllInvoiceEntries()
    {
        $repository = $this->entityManager->getRepository(InvoiceEntry::class);
        $invoiceEntries = $repository->findAll();

        if (!$invoiceEntries) {
            return [];
        }

        $invoiceEntriesJson = [];

        foreach ($invoiceEntries as $invoiceEntry) {
            $invoiceEntriesJson[] = [
                'id' => $invoiceEntry->getId(),
                'invoiceId' => $invoiceEntry->getInvoice()->getId(),
                'description' => $invoiceEntry->getDescription(),
                'account' => $invoiceEntry->getAccount(),
                'product' => $invoiceEntry->getProduct(),
                'price' => $invoiceEntry->getPrice(),
                'amount' => $invoiceEntry->getAmount(),
            ];
        }

        return $invoiceEntriesJson;
    }

    /**
     * Get specific invoiceEntry by id.
     *
     * @param $invoiceEntryId
     *
     * @return array
     */
    public function getInvoiceEntry($invoiceEntryId)
    {
        if (!(int) $invoiceEntryId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryId]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'InvoiceEntry with id '.$invoiceEntryId.' not found');
        }

        return $this->getInvoiceEntryArray($invoiceEntry);
    }

    /**
     * Get invoice entry as array.
     *
     * @param \Billing\Entity\InvoiceEntry $invoiceEntry
     *
     * @return array
     */
    private function getInvoiceEntryArray(InvoiceEntry $invoiceEntry)
    {
        return [
            'id' => $invoiceEntry->getId(),
            'invoiceId' => $invoiceEntry->getInvoice()->getId(),
            'description' => $invoiceEntry->getDescription(),
            'account' => $invoiceEntry->getAccount(),
            'product' => $invoiceEntry->getProduct(),
            'entryType' => $invoiceEntry->getEntryType(),
            'amount' => $invoiceEntry->getAmount(),
            'price' => $invoiceEntry->getPrice(),
            'worklogIds' => array_reduce($invoiceEntry->getWorklogs()->toArray(), function ($carry, Worklog $worklog) {
                $carry[$worklog->getWorklogId()] = true;

                return $carry;
            }, []),
            'expenseIds' => array_reduce($invoiceEntry->getExpenses()->toArray(), function ($carry, Expense $expense) {
                $carry[$expense->getExpenseId()] = true;

                return $carry;
            }, []),
        ];
    }

    /**
     * Post new invoiceEntry, creating a new entity referenced by the returned id.
     *
     * @param $invoiceEntryData
     *
     * @return array invoiceEntryData
     */
    public function postInvoiceEntry($invoiceEntryData)
    {
        if (empty($invoiceEntryData['invoiceId']) || !(int) ($invoiceEntryData['invoiceId'])) {
            throw new HttpException(400, "Expected integer value for 'invoiceId' in request");
        }

        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $invoiceRepository->findOneBy(['id' => $invoiceEntryData['invoiceId']]);

        if (!$invoice) {
            throw new HttpException(400, 'Invoice with id '.$invoiceEntryData['invoiceId'].' not found');
        }

        $invoiceEntry = new InvoiceEntry();
        $invoiceEntry->setInvoice($invoice);

        $this->setInvoiceEntryValuesFromData($invoiceEntry, $invoiceEntryData);

        $this->entityManager->persist($invoiceEntry);
        $this->entityManager->flush();

        return $this->getInvoiceEntryArray($invoiceEntry);
    }

    /**
     * Set invoiceEntry from data array.
     *
     * @param \Billing\Entity\InvoiceEntry $invoiceEntry
     * @param array                        $invoiceEntryData
     *
     * @return \Billing\Entity\InvoiceEntry
     */
    private function setInvoiceEntryValuesFromData(InvoiceEntry $invoiceEntry, array $invoiceEntryData)
    {
        if (isset($invoiceEntryData['entryType'])) {
            $invoiceEntry->setEntryType($invoiceEntryData['entryType']);
        }

        if (isset($invoiceEntryData['amount'])) {
            $invoiceEntry->setAmount($invoiceEntryData['amount']);
        }

        if (isset($invoiceEntryData['price'])) {
            $invoiceEntry->setPrice($invoiceEntryData['price']);
        }

        if (isset($invoiceEntryData['description'])) {
            $invoiceEntry->setDescription($invoiceEntryData['description']);
        }

        if (isset($invoiceEntryData['account'])) {
            $invoiceEntry->setAccount($invoiceEntryData['account']);
        }

        if (isset($invoiceEntryData['product'])) {
            $invoiceEntry->setProduct($invoiceEntryData['product']);
        }

        // If worklogIds has been changed.
        if (isset($invoiceEntryData['worklogIds'])) {
            $worklogs = $invoiceEntry->getWorklogs();

            // Remove de-selected worklogs.
            foreach ($worklogs as $worklog) {
                if (!\in_array($worklog->getWorklogId(), $invoiceEntryData['worklogIds'])) {
                    $this->entityManager->remove($worklog);
                }
            }

            // Add not-added worklogs.
            foreach ($invoiceEntryData['worklogIds'] as $worklogId) {
                $worklog = $this->worklogRepository->findOneBy(['worklogId' => $worklogId]);

                if (null === $worklog) {
                    $worklog = new Worklog();
                    $worklog->setWorklogId($worklogId);
                    $worklog->setInvoiceEntry($invoiceEntry);

                    $this->entityManager->persist($worklog);
                } else {
                    if ($worklog->getInvoiceEntry()->getId() === $invoiceEntry->getId()) {
                        throw new HttpException(
                            'Used by other invoice entry.'
                        );
                    }
                }
            }
        }

        // If expenseIds has been changed.
        if (isset($invoiceEntryData['expenseIds'])) {
            $expenses = $invoiceEntry->getExpenses();

            // Remove de-selected expenses.
            foreach ($expenses as $expense) {
                if (!\in_array($expense->getExpenseId(), $invoiceEntryData['expenseIds'])) {
                    $this->entityManager->remove($expense);
                }
            }

            // Add not-added expenses.
            foreach ($invoiceEntryData['expenseIds'] as $expenseId) {
                $expense = $this->expenseRepository->findOneBy(['expenseId' => $expenseId]);

                if (null === $expense) {
                    $expense = new Expense();
                    $expense->setExpenseId($expenseId);
                    $expense->setInvoiceEntry($invoiceEntry);

                    $this->entityManager->persist($expense);
                } else {
                    if ($expense->getInvoiceEntry()->getId() === $invoiceEntry->getId()) {
                        throw new HttpException(
                            'Used by other invoice entry.'
                        );
                    }
                }
            }
        }

        return $invoiceEntry;
    }

    /**
     * Put specific invoiceEntry, replacing the invoiceEntry referenced by the given id.
     *
     * @param $invoiceEntryData
     *
     * @return array
     */
    public function putInvoiceEntry($invoiceEntryData)
    {
        if (empty($invoiceEntryData['id']) || !(int) ($invoiceEntryData['id'])) {
            throw new HttpException(400, "Expected integer value for 'id' in request");
        }

        $repository = $this->entityManager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryData['id']]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'Unable to update invoiceEntry with id '.$invoiceEntryData['id'].' as it does not already exist');
        }

        $invoiceEntry = $this->setInvoiceEntryValuesFromData($invoiceEntry, $invoiceEntryData);

        $this->entityManager->persist($invoiceEntry);
        $this->entityManager->flush();

        return $this->getInvoiceEntryArray($invoiceEntry);
    }

    /**
     * Delete specific invoice entry referenced by the given id.
     *
     * @param $invoiceEntryId
     */
    public function deleteInvoiceEntry($invoiceEntryId)
    {
        if (empty($invoiceEntryId) || !(int) $invoiceEntryId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryId]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'InvoiceEntry with id '.$invoiceEntryId.' did not exist');
        }

        $this->entityManager->remove($invoiceEntry);
        $this->entityManager->flush();
    }

    /**
     * Record an invoice.
     *
     * @param $invoiceId
     *
     * @return array
     */
    public function recordInvoice($invoiceId)
    {
        $invoice = $this->entityManager->getRepository(Invoice::class)
            ->find($invoiceId);
        $invoice->setRecorded(true);

        // Set billed field in Jira for each worklog.
        foreach ($invoice->getInvoiceEntries() as $invoiceEntry) {
            foreach ($invoiceEntry->getWorklogs() as $worklog) {
                $this->put('/rest/tempo-timesheets/4/worklogs/'.$worklog->getWorklogId(), [
                    'attributes' => [
                        '_Billed_' => [
                            'value' => true,
                        ],
                    ],
                ]);

                $worklog->setIsBilled(true);
            }

            // @TODO: Record billed in Jira if possible.
            foreach ($invoiceEntry->getExpenses() as $expens) {
                $expens->setIsBilled(true);
            }
        }

        $this->entityManager->flush();

        return $this->getInvoiceArray($invoice);
    }

    /**
     * Get specific project by Jira project ID.
     *
     * @param $jiraProjectId
     *
     * @return \Billing\Entity\Project|object
     */
    public function getJiraProject($jiraProjectId)
    {
        if (!(int) $jiraProjectId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        try {
            $result = $this->getProject($jiraProjectId);
        } catch (HttpException $e) {
            throw $e;
        }

        $repository = $this->entityManager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $jiraProjectId]);

        if (!$project) {
            $project = new Project();
            $this->entityManager->persist($project);
        }

        $project->setJiraId($result->id);
        $project->setJiraKey($result->key);
        $project->setName($result->name);
        $project->setUrl($result->self);
        $project->setAvatarUrl($result->avatarUrls->{'48x48'});

        $this->entityManager->flush();

        return $project;
    }

    /**
     * Get project worklogs with extra metadata.
     *
     * @param $projectId
     *
     * @return mixed
     */
    public function getProjectWorklogsWithMetadata($projectId)
    {
        $worklogs = $this->getProjectWorklogs($projectId);
        $project = $this->getProject($projectId);
        $versions = $project->versions;
        $epics = $this->getProjectEpics($projectId);

        // Get custom fields.
        $customFields = $customFields = $this->get('/rest/api/2/field');

        // Get Epic name field id.
        $customFieldEpicName = $customFieldEpicLink = array_search(
            'Epic Name',
            array_column($customFields, 'name')
        );
        $epicNameCustomFieldId = $customFields[$customFieldEpicName]->{'id'};

        foreach ($worklogs as $worklog) {
            $issue = $worklog->issue;

            foreach ($epics as $epic) {
                if ($epic->key === $issue->epicKey) {
                    $issue->epicName = $epic->fields->{$epicNameCustomFieldId};
                    break;
                }
            }

            $issueVersions = [];
            $issueVersionKeys = array_values($issue->versions);

            foreach ($issueVersionKeys as $issueVersionKey) {
                foreach ($versions as $version) {
                    if ((int) $version->id === $issueVersionKey) {
                        $issueVersions[$issueVersionKey] = $version->name;
                    }
                }
            }

            $issue->versions = $issueVersions;

            $worklogEntity = $this->worklogRepository->findOneBy(['worklogId' => $worklog->tempoWorklogId]);

            if (null !== $worklogEntity) {
                $worklog->addedToInvoiceEntryId = $worklogEntity->getInvoiceEntry()->getId();

                $worklog->billed = $worklogEntity->getIsBilled();
            }
        }

        return $worklogs;
    }

    /**
     * Get project expenses.
     *
     * @param $projectId
     *
     * @return array
     */
    public function getProjectExpenses($projectId)
    {
        $allExpenses = $this->getExpenses();
        $issues = array_reduce($this->getProjectIssues($projectId), function ($carry, $issue) {
            $carry[$issue->id] = $issue;

            return $carry;
        }, []);

        $expenses = [];
        foreach ($allExpenses as $key => $expense) {
            if ('ISSUE' === $expense->scope->scopeType) {
                if (\in_array($expense->scope->scopeId, array_keys($issues))) {
                    $expense->issue = $issues[$expense->scope->scopeId];
                    $expenses[] = $expense;
                }
            }
        }

        return $expenses;
    }

    /**
     * Get project expenses with metadata about version, epic, etc.
     *
     * @param $projectId
     *
     * @return array
     */
    public function getProjectExpensesWithMetadata($projectId)
    {
        $expenses = $this->getProjectExpenses($projectId);
        $epics = $this->getProjectEpics($projectId);

        // Get custom fields.
        $customFields = $customFields = $this->get('/rest/api/2/field');

        // Get Epic link field id.
        $customFieldEpicId = $customFieldEpicLink = array_search(
            'Epic Link',
            array_column($customFields, 'name')
        );
        $epicNameCustomFieldIdId = $customFields[$customFieldEpicId]->{'id'};

        // Get Epic name field id.
        $customFieldEpicName = $customFieldEpicLink = array_search(
            'Epic Name',
            array_column($customFields, 'name')
        );
        $epicNameCustomFieldId = $customFields[$customFieldEpicName]->{'id'};

        foreach ($expenses as $expense) {
            foreach ($epics as $epic) {
                if ($epic->key === $expense->issue->fields->{$epicNameCustomFieldIdId}) {
                    $expense->issue->epicKey = $epic->key;
                    $expense->issue->epicName = $epic->fields->{$epicNameCustomFieldId};
                    break;
                }
            }

            $expense->issue->versions = array_reduce($expense->issue->fields->fixVersions, function ($carry, $version) {
                $carry->{$version->id} = $version->name;

                return $carry;
            }, (object) []);

            $expenseEntity = $this->expenseRepository->findOneBy(['expenseId' => $expense->id]);

            if (null !== $expenseEntity) {
                $expense->addedToInvoiceEntryId = $expenseEntity->getInvoiceEntry()->getId();

                $expense->billed = $expenseEntity->getIsBilled();
            }
        }

        return $expenses;
    }

    /**
     * Get epics for project.
     *
     * @param $projectId
     *
     * @return array
     */
    public function getProjectEpics($projectId)
    {
        return $this->getProjectIssues($projectId, 'Epic');
    }

    /**
     * Get project issues of a given issue type.
     *
     * @param $projectId
     * @param null $issueType
     *
     * @return array
     */
    public function getProjectIssues($projectId, $issueType = null)
    {
        $epics = [];

        $jql = 'project='.$projectId;

        if (null !== $issueType) {
            $jql = $jql.' and issuetype='.$issueType;
        }

        $startAt = 0;
        while (true) {
            $result = $this->get('/rest/api/2/search', [
                'jql' => $jql,
                'maxResults' => 50,
                'startAt' => $startAt,
            ]);
            $epics = array_merge($epics, $result->issues);

            $startAt = $startAt + 50;

            if ($startAt > $result->total) {
                break;
            }
        }

        return $epics;
    }
}
