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
use Billing\Entity\Customer;
use Billing\Entity\Invoice;
use Billing\Entity\InvoiceEntry;
use Billing\Entity\JiraIssue;
use Billing\Entity\Project;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\EntityManagerInterface;

class BillingService extends JiraService
{
    private $entityManager;

    /**
     * Constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param $jiraUrl
     * @param $tokenStorage
     * @param $customerKey
     * @param $pemPath
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        $jiraUrl,
        $tokenStorage,
        $customerKey,
        $pemPath
    ) {
        parent::__construct($jiraUrl, $tokenStorage, $customerKey, $pemPath);

        $this->entityManager = $entityManager;
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

        $invoices = $project->getInvoices();
        $invoicesJson = [];

        foreach ($invoices as $invoice) {
            $invoicesJson[] = [
                'invoiceId' => $invoice->getId(),
                'name' => $invoice->getName(),
                'jiraId' => $invoice->getProject()->getJiraId(),
                'recorded' => $invoice->getRecorded(),
                'created' => $invoice->getCreated(),
            ];
        }

        return $invoicesJson;
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

        $invoicesJson = [];

        foreach ($invoices as $invoice) {
            $invoicesJson[] = [
                'invoiceId' => $invoice->getId(),
                'invoiceName' => $invoice->getName(),
                'jiraProjectId' => $invoice->getProject()->getJiraId(),
                'jiraProjectName' => $invoice->getProject()->getName(),
                'recorded' => $invoice->getRecorded(),
                'created' => $invoice->getCreated(),
            ];
        }

        return $invoicesJson;
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

        return [
            'name' => $invoice->getName(),
            'jiraId' => $invoice->getProject()->getJiraId(),
            'recorded' => $invoice->getRecorded(),
            'created' => $invoice->getCreated(),
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

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return [
            'invoiceId' => $invoice->getId(),
            'name' => $invoice->getName(),
            'jiraId' => $invoice->getProject()->getJiraId(),
            'recorded' => $invoice->getRecorded(),
            'created' => $invoice->getCreated(),
        ];
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

        if (isset($invoiceData['recorded']) && !\in_array($invoiceData['recorded'], [true, false])) {
            throw new HttpException(400, "Expected boolean value for 'recorded' in request");
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceData['id']]);

        if (!$invoice) {
            throw new HttpException(404, 'Unable to update invoice with id '.$invoiceData['id'].' as it does not already exist');
        }

        if (!empty($invoiceData['name'])) {
            $invoice->setName($invoiceData['name']);
        }

        if (isset($invoiceData['recorded'])) {
            $invoiceRecorded = $invoiceData['recorded'];
            $invoice->setRecorded($invoiceRecorded);
        }

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return [
            'name' => $invoice->getName(),
            'jiraId' => $invoice->getProject()->getJiraId(),
            'recorded' => $invoice->getRecorded(),
            'created' => $invoice->getCreated(),
        ];
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
            $jiraIssueIds = [];
            $jiraIssues = $invoiceEntry->getJiraIssues();

            foreach ($jiraIssues as $jiraIssue) {
                $jiraIssueIds[] = $jiraIssue->getIssueId();
            }

            $invoiceEntry = [
                'id' => $invoiceEntry->getId(),
                'name' => $invoiceEntry->getName(),
                'invoiceId' => $invoiceEntry->getInvoice()->getId(),
                'description' => $invoiceEntry->getDescription(),
                'account' => $invoiceEntry->getAccount(),
                'product' => $invoiceEntry->getProduct(),
                'amount' => $invoiceEntry->getAmount(),
                'price' => $invoiceEntry->getPrice(),
            ];

            if (\count($jiraIssueIds) > 0) {
                $invoiceEntry['jiraIssueIds'] = $jiraIssueIds;
            }

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
                'name' => $invoiceEntry->getName(),
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
     * @param invoiceEntryId
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

        $jiraIssueIds = [];
        $jiraIssues = $invoiceEntry->getJiraIssues();

        foreach ($jiraIssues as $jiraIssue) {
            $jiraIssueIds[] = $jiraIssue->getIssueId();
        }

        $invoiceEntry = [
            'name' => $invoiceEntry->getName(),
            'invoiceId' => $invoiceEntry->getInvoice()->getId(),
            'description' => $invoiceEntry->getDescription(),
            'account' => $invoiceEntry->getAccount(),
            'product' => $invoiceEntry->getProduct(),
            'amount' => $invoiceEntry->getAmount(),
            'price' => $invoiceEntry->getPrice(),
        ];

        if (\count($jiraIssueIds) > 0) {
            $invoiceEntry['jiraIssueIds'] = $jiraIssueIds;
        }

        return $invoiceEntry;
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

        if (empty($invoiceEntryData['amount']) || !(float) ($invoiceEntryData['amount'])) {
            throw new HttpException(400, "Expected numerical value for 'amount' in request");
        }

        if (empty($invoiceEntryData['price']) || !(float) ($invoiceEntryData['price'])) {
            throw new HttpException(400, "Expected numerical value for 'price' in request");
        }

        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $invoiceRepository->findOneBy(['id' => $invoiceEntryData['invoiceId']]);

        if (!$invoice) {
            throw new HttpException(400, 'Invoice with id '.$invoiceEntryData['invoiceId'].' not found');
        }

        $invoiceEntry = new InvoiceEntry();
        $invoiceEntry->setInvoice($invoice);
        $invoiceEntry->setAmount($invoiceEntryData['amount']);
        $invoiceEntry->setPrice($invoiceEntryData['price']);

        if (!empty($invoiceEntryData['name'])) {
            $invoiceEntry->setName($invoiceEntryData['name']);
        }

        if (!empty($invoiceEntryData['description'])) {
            $invoiceEntry->setDescription($invoiceEntryData['description']);
        }

        if (!empty($invoiceEntryData['account'])) {
            $invoiceEntry->setAccount($invoiceEntryData['account']);
        }

        if (!empty($invoiceEntryData['product'])) {
            $invoiceEntry->setProduct($invoiceEntryData['product']);
        }

        $response = [
            'id' => $invoiceEntry->getId(),
            'name' => $invoiceEntry->getName(),
            'jiraProjectId' => $invoiceEntry->getInvoice()->getProject()->getJiraId(),
            'invoiceId' => $invoiceEntry->getInvoice()->getId(),
            'description' => $invoiceEntry->getDescription(),
            'account' => $invoiceEntry->getAccount(),
            'product' => $invoiceEntry->getProduct(),
            'amount' => $invoiceEntry->getAmount(),
            'price' => $invoiceEntry->getPrice(),
        ];

        if (!empty($invoiceEntryData['jiraIssueIds'])) {
            $jiraIssueRepository = $this->entityManager->getRepository(JiraIssue::class);

            foreach ($invoiceEntryData['jiraIssueIds'] as $jiraIssueId) {
                $jiraIssue = $jiraIssueRepository->findOneBy(['issueId' => $jiraIssueId]);

                if (!$jiraIssue) {
                    throw new HttpException(400, 'JiraIssue with id '.$jiraIssueId.' not found');
                }

                $invoiceEntry->addJiraIssue($jiraIssue);
            }

            $response['jiraIssueIds'] = $invoiceEntryData['jiraIssueIds'];
        }

        $this->entityManager->persist($invoiceEntry);
        $this->entityManager->flush();

        return $response;
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

        if (empty($invoiceEntryData['amount']) || !(float) ($invoiceEntryData['amount'])) {
            throw new HttpException(400, "Expected numerical value for 'amount' in request");
        }

        if (empty($invoiceEntryData['price']) || !(float) ($invoiceEntryData['price'])) {
            throw new HttpException(400, "Expected numerical value for 'price' in request");
        }

        $repository = $this->entityManager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryData['id']]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'Unable to update invoiceEntry with id '.$invoiceEntryData['id'].' as it does not already exist');
        }

        $invoiceEntry->setAmount($invoiceEntryData['amount']);
        $invoiceEntry->setPrice($invoiceEntryData['price']);

        if (!empty($invoiceEntryData['name'])) {
            $invoiceEntry->setName($invoiceEntryData['name']);
        }

        if (!empty($invoiceEntryData['description'])) {
            $invoiceEntry->setDescription($invoiceEntryData['description']);
        }

        if (!empty($invoiceEntryData['account'])) {
            $invoiceEntry->setAccount($invoiceEntryData['account']);
        }

        if (!empty($invoiceEntryData['product'])) {
            $invoiceEntry->setProduct($invoiceEntryData['product']);
        }

        $response = [
            'name' => $invoiceEntry->getName(),
            'jiraId' => $invoiceEntry->getInvoice()->getProject()->getJiraId(),
            'invoiceId' => $invoiceEntry->getInvoice()->getId(),
            'description' => $invoiceEntry->getDescription(),
            'account' => $invoiceEntry->getAccount(),
            'product' => $invoiceEntry->getProduct(),
            'amount' => $invoiceEntry->getAmount(),
            'price' => $invoiceEntry->getPrice(),
        ];

        if (!empty($invoiceEntryData['jiraIssueIds'])) {
            $jiraIssueRepository = $this->entityManager->getRepository(JiraIssue::class);

            foreach ($invoiceEntryData['jiraIssueIds'] as $jiraIssueId) {
                $jiraIssue = $jiraIssueRepository->findOneBy(['issueId' => $jiraIssueId]);

                if (!$jiraIssue) {
                    throw new HttpException(400, 'JiraIssue with id '.$jiraIssueId.' not found');
                }

                $invoiceEntry->addJiraIssue($jiraIssue);
            }

            $response['jiraIssueIds'] = $invoiceEntryData['jiraIssueIds'];
        }

        $this->entityManager->persist($invoiceEntry);
        $this->entityManager->flush();

        return $response;
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
     * Get jiraIssues for project.
     *
     * @param $jiraProjectId
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getJiraIssues($jiraProjectId)
    {
        $jiraIssues = [];

        if (!(int) $jiraProjectId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $jiraProjectId]);

        if (!$project) {
            throw new HttpException(404, 'Project with id '.$jiraProjectId.' not found');
        }

        $start = 0;

        while (true) {
            try {
                // @TODO: this API call does not currently work as it returns all Jira issues, regardless of jiraProjectId
                $results = $this->get('rest/api/2/search?jql=project='.$jiraProjectId.'&startAt='.$start);
            } catch (HttpException $e) {
                throw $e;
            }
            foreach ($results->issues as $jiraIssueResult) {
                $repository = $this->entityManager->getRepository(JiraIssue::class);
                $jiraIssue = $repository->findOneBy(['issueId' => $jiraIssueResult->id]);

                if (!$jiraIssue) {
                    $jiraIssue = new JiraIssue();
                }

                $jiraIssue->setIssueId($jiraIssueResult->id);
                $jiraIssue->setProject($project);

                if (!empty($jiraIssueResult->fields->timespent)) {
                    $jiraIssue->setTimeSpent($jiraIssueResult->fields->timespent);
                }

                $jiraIssue->setCreated(new \DateTime($jiraIssueResult->fields->created));
                $jiraIssue->setFinished(new \DateTime($jiraIssueResult->fields->resolutiondate));
                $jiraIssue->setSummary($jiraIssueResult->fields->summary);

                // @TODO: should we add other users than the assignee?
                if (!empty($jiraIssueResult->fields->assignee->key)) {
                    $jiraIssue->setJiraUsers([$jiraIssueResult->fields->assignee->key]);
                }

                $issue = [
                    'issueId' => $jiraIssue->getIssueId(),
                    'summary' => $jiraIssue->getSummary(),
                    'created' => $jiraIssue->getCreated(),
                    'finished' => $jiraIssue->getFinished(),
                    'jiraUsers' => $jiraIssue->getJiraUsers(),
                    'timeSpent' => $jiraIssue->getTimeSpent(),
                ];

                if (null !== $jiraIssue->getInvoiceEntryId()) {
                    // @TODO: fix misleading getInvoiceEntryId naming - the function actually returns an InvoiceEntry object
                    $issue['invoiceEntryId'] = $jiraIssue->getInvoiceEntryId()->getId();
                } else {
                    $issue['invoiceEntryId'] = null;
                }

                $jiraIssues[] = $issue;
                $this->entityManager->persist($jiraIssue);
            }

            $start += 50;

            if ($start > $results->total) {
                break;
            }
        }
        $this->entityManager->flush();

        return $jiraIssues;
    }

    /**
     * Get specific customer by id.
     *
     * @param $customerId
     *
     * @return array
     */
    public function getCustomer($customerId)
    {
        if (!(int) $customerId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Customer::class);
        $customer = $repository->findOneBy(['id' => $customerId]);

        if (!$customer) {
            throw new HttpException(404, 'Customer with id '.$customerId.' not found');
        }

        return [
            'name' => $customer->getName(),
            'att' => $customer->getAtt(),
            'cvr' => $customer->getCVR(),
            'ean' => $customer->getEAN(),
            'debtor' => $customer->getDebtor(),
        ];
    }

    /**
     * Post new customer, creating a new entity referenced by the returned id.
     *
     * @param $customerData
     *
     * @return array customerData
     */
    public function postCustomer($customerData)
    {
        if (empty($customerData['name'])) {
            throw new HttpException(400, "Expected 'name' in request");
        } elseif (empty($customerData['att'])) {
            throw new HttpException(400, "Expected 'att' in request");
        } elseif (empty($customerData['cvr'])) {
            throw new HttpException(400, "Expected 'cvr' in request");
        } elseif (empty($customerData['ean'])) {
            throw new HttpException(400, "Expected 'ean' in request");
        } elseif (empty($customerData['debtor'])) {
            throw new HttpException(400, "Expected 'debtor' in request");
        }

        $customer = new Customer();
        $customer->setName($customerData['name']);
        $customer->setAtt($customerData['att']);
        $customer->setCVR($customerData['cvr']);
        $customer->setEAN($customerData['ean']);
        $customer->setDebtor($customerData['debtor']);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return [
            'customerId' => $customer->getId(),
            'name' => $customer->getName(),
            'att' => $customer->getAtt(),
            'cvr' => $customer->getCVR(),
            'ean' => $customer->getEAN(),
            'debtor' => $customer->getDebtor(),
        ];
    }

    /**
     * Put specific customer, replacing the customer referenced by the given id.
     *
     * @param $customerData
     *
     * @return array customerData
     */
    public function putCustomer($customerData)
    {
        if (empty($customerData['name'])) {
            throw new HttpException(400, "Expected 'name' in request");
        } elseif (empty($customerData['att'])) {
            throw new HttpException(400, "Expected 'att' in request");
        } elseif (empty($customerData['cvr'])) {
            throw new HttpException(400, "Expected 'cvr' in request");
        } elseif (empty($customerData['ean'])) {
            throw new HttpException(400, "Expected 'ean' in request");
        } elseif (empty($customerData['debtor'])) {
            throw new HttpException(400, "Expected 'debtor' in request");
        }

        $repository = $this->entityManager->getRepository(Customer::class);
        $customer = $repository->findOneBy(['id' => $customerData['customerId']]);

        if (!$customer) {
            throw new HttpException(400, 'Customer with id '.$customerData['customerId'].' not found');
        }

        $customer->setName($customerData['name']);
        $customer->setAtt($customerData['att']);
        $customer->setCVR($customerData['cvr']);
        $customer->setEAN($customerData['ean']);
        $customer->setDebtor($customerData['debtor']);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return [
            'name' => $customer->getName(),
            'att' => $customer->getAtt(),
            'cvr' => $customer->getCVR(),
            'ean' => $customer->getEAN(),
            'debtor' => $customer->getDebtor(),
        ];
    }

    /**
     * Delete specific customer referenced by the given id.
     *
     * @param $customerId
     */
    public function deleteCustomer($customerId)
    {
        if (empty($customerId) || !(int) $customerId) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Customer::class);
        $customer = $repository->findOneBy(['id' => $customerId]);

        if (!$customer) {
            throw new HttpException(404, 'Customer with id '.$customerId.' did not exist');
        }

        $this->entityManager->remove($customer);
        $this->entityManager->flush();
    }

    /**
     * Get specific fixed version by the given fixed version and Jira Project ID.
     *
     * @param $fixedVersion
     * @param $jiraId
     *
     * @return array
     */
    public function getFixedVersionForProject($fixedVersion, $jiraProjectId)
    {
        if (empty($fixedVersion) || !(int) $fixedVersion) {
            throw new HttpException(400, "Expected integer value in request for 'fixedVersion'");
        }

        if (empty($jiraProjectId) || !(int) $jiraProjectId) {
            throw new HttpException(400, "Expected integer value in request for 'jiraProjectId'");
        }

        $params = [
            'jql' => 'fixVersion='.$fixedVersion,
            'project' => $jiraProjectId,
            'maxResults' => 50,
            'startAt' => 0
        ];

        try {
            $results = $this->get('/rest/api/2/search', $params);
        } catch (HttpException $e) {
            throw $e;
        }

        // @TODO: return appropriate values, not just the entire result
        //$result->id;

        return $results;
    }
}
