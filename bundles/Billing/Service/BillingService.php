<?php

namespace Billing\Service;

use App\Service\JiraService;
use Billing\Entity\Customer;
use Billing\Entity\Invoice;
use Billing\Entity\InvoiceEntry;
use Billing\Entity\JiraIssue;
use Billing\Entity\Project;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Doctrine\ORM\EntityManagerInterface;

class BillingService
{
    private $entityManager;
    private $jiraService;

    /**
     * Constructor.
     */
    public function __construct(
        JiraService $jiraService,
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->jiraService = $jiraService;
    }

    /**
     * Get invoices for specific Jira project
     * @param jira_id
     * @return array
     */
    public function getInvoices($jiraProjectId)
    {
        if (!intval($jiraProjectId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $jiraProjectId]);

        if (!$project) {
            throw new HttpException(404, 'Project with id ' . $jiraProjectId . ' not found');
        }

        $invoices = $project->getInvoices();

        $invoicesJson = [];

        foreach ($invoices AS $invoice) {
            $invoicesJson[] = [
                'invoiceId' => $invoice->getId(),
                'name'      => $invoice->getName(),
                'jiraId'    => $invoice->getProject()->getJiraId(),
                'recorded'  => $invoice->getRecorded(),
                'created'   => $invoice->getCreated()
            ];
        }

        return $invoicesJson;
    }

    /**
     * Get specific invoice by id
     * @param invoiceId
     * @return array
     */
    public function getInvoice($invoiceId)
    {
        if (!intval($invoiceId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceId]);

        if (!$invoice) {
            throw new HttpException(404, 'Invoice with id ' . $invoiceId . ' not found');
        }

        if ($invoice->getRecorded() == "1") {
            $recorded = true;
        }
        else {
            $recorded = false;
        }

        return [
            'name'     => $invoice->getName(),
            'jiraId'   => $invoice->getProject()->getJiraId(),
            'recorded' => $recorded,
            'created'  => $invoice->getCreated()
        ];
    }

    /**
     * Post new invoice, creating a new entity referenced by the returned id
     * @return array invoiceData
     */
    public function postInvoice($invoiceData)
    {
        if (empty($invoiceData['projectId']) || !intval($invoiceData['projectId'])) {
            throw new HttpException(400, "Expected integer value for 'projectId' in request");
        }

        if (empty($invoiceData['name'])) {
            throw new HttpException(400, "Expected 'name' in request");
        }

        $repository = $this->entityManager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $invoiceData['projectId']]);

        if (!$project) {
            throw new HttpException(404, "Project with id " . $invoiceData['projectId'] . " not found");
        }

        $invoice = new Invoice();
        $invoice->setName($invoiceData['name']);
        $invoice->setProject($project);
        $invoice->setRecorded(false);
        $invoice->setCreated(new \DateTime("now"));

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return [
            'invoiceId' => $invoice->getId(),
            'name'      => $invoice->getName(),
            'jiraId'    => $invoice->getProject()->getJiraId(),
            'recorded'  => $invoice->getRecorded(),
            'created'   => $invoice->getCreated()
        ];
    }

    /**
     * Put specific invoice, replacing the invoice referenced by the given id
     * @param invoiceData
     * @return array
     */
    public function putInvoice($invoiceData)
    {
        if (empty($invoiceData['id']) || !intval($invoiceData['id'])) {
            throw new HttpException(400, "Expected integer value for 'id' in request");
        }

        if (isset($invoiceData['recorded']) && !in_array($invoiceData['recorded'], [true, false])) {
            throw new HttpException(400, "Expected boolean value for 'recorded' in request");
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceData['id']]);

        if (!$invoice) {
            throw new HttpException(404, 'Unable to update invoice with id ' . $invoiceData['id'] . ' as it does not already exist');
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
            'name'      => $invoice->getName(),
            'jiraId'    => $invoice->getProject()->getJiraId(),
            'recorded'  => $invoice->getRecorded(),
            'created'   => $invoice->getCreated()
        ];
    }

    /**
     * Delete specific invoice referenced by the given id
     * @param invoiceId
     */
    public function deleteInvoice($invoiceId)
    {
        if (empty($invoiceId) || !intval($invoiceId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceId]);

        if (!$invoice) {
            throw new HttpException(404, 'Invoice with id ' . $invoiceId . ' did not exist');
        }

        $this->entityManager->remove($invoice);
        $this->entityManager->flush();
    }

    /**
     * Get invoiceEntries for specific invoice
     * @param invoice_id
     * @return array
     */
    public function getInvoiceEntries($invoiceId)
    {
        if (!intval($invoiceId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceId]);

        $invoiceEntries = $invoice->getInvoiceEntries();

        $invoiceEntriesJson = [];

        foreach ($invoiceEntries AS $invoiceEntry) {
            $invoiceEntriesJson[] = [
                'invoiceEntryId' => $invoiceEntry->getId(),
                'name'           => $invoiceEntry->getName(),
                'invoiceId'      => $invoiceEntry->getInvoice()->getId(),
                'description'    => $invoiceEntry->getDescription(),
                'account'        => $invoiceEntry->getAccount(),
                'product'        => $invoiceEntry->getProduct()
            ];
        }

        return $invoiceEntriesJson;
    }

    /**
     * Get specific invoiceEntry by id
     * @param invoice_entry_id
     * @return array
     */
    public function getInvoiceEntry($invoiceEntryId)
    {
        if (!intval($invoiceEntryId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryId]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'InvoiceEntry with id ' . $invoiceEntryId . ' not found');
        }

        return [
            'name'          => $invoiceEntry->getName(),
            'invoiceId'     => $invoiceEntry->getInvoice()->getId(),
            'description'   => $invoiceEntry->getDescription(),
            'account'       => $invoiceEntry->getAccount(),
            'product'       => $invoiceEntry->getProduct()
        ];

    }

    /**
     * Post new invoiceEntry, creating a new entity referenced by the returned id
     * @return array invoiceEntryData
     */
    public function postInvoiceEntry($invoiceEntryData)
    {
        if (empty($invoiceEntryData['invoiceId']) || !intval($invoiceEntryData['invoiceId'])) {
            throw new HttpException(400, "Expected integer value for 'invoiceId' in request");
        }

        if (empty($invoiceEntryData['name'])) {
            throw new HttpException(400, "Missing 'name' for new invoice entry in request");
        }

        $invoiceRepository = $this->entityManager->getRepository(Invoice::class);
        $invoice = $invoiceRepository->findOneBy(['id' => $invoiceEntryData['invoiceId']]);

        if (!$invoice) {
            throw new HttpException(400, "Invoice with id " . $invoiceEntryData['invoiceId'] . " not found");
        }

        $invoiceEntry = new InvoiceEntry();
        $invoiceEntry->setName($invoiceEntryData['name']);
        $invoiceEntry->setDescription($invoiceEntryData['description']);
        $invoiceEntry->setAccount($invoiceEntryData['account']);
        $invoiceEntry->setProduct($invoiceEntryData['product']);
        $invoiceEntry->setInvoice($invoice);

        $response = [
            'invoiceEntryId'    => $invoiceEntry->getId(),
            'name'              => $invoiceEntry->getName(),
            'jiraProjectId'     => $invoiceEntry->getInvoice()->getProject()->getJiraId(),
            'invoiceId'         => $invoiceEntry->getInvoice()->getId(),
            'description'       => $invoiceEntry->getDescription(),
            'account'           => $invoiceEntry->getAccount(),
            'product'           => $invoiceEntry->getProduct()
        ];

        if (!empty($invoiceEntryData['jiraIssueIds'])) {
            $jiraIssueRepository = $this->entityManager->getRepository(JiraIssue::class);

            foreach ($invoiceEntryData['jiraIssueIds'] as $jiraIssueId) {
                $jiraIssue = $jiraIssueRepository->findOneBy(['issueId' => $jiraIssueId]);

                if (!$jiraIssue) {
                    throw new HttpException(400, "JiraIssue with id " . $jiraIssueId . " not found");
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
     * Put specific invoiceEntry, replacing the invoiceEntry referenced by the given id
     * @param invoiceEntryData
     * @return array
     */
    public function putInvoiceEntry($invoiceEntryData)
    {
        if (empty($invoiceEntryData['id']) || !intval($invoiceEntryData['id'])) {
            throw new HttpException(400, "Expected integer value for 'id' in request");
        }

        $repository = $this->entityManager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryData['id']]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'Unable to update invoiceEntry with id ' . $invoiceEntryData['id'] . ' as it does not already exist');
        }

        if (!empty($invoiceEntryData['name'])) {
            $invoiceEntry->setName($invoiceEntryData['name']);
        }

        $this->entityManager->persist($invoiceEntry);
        $this->entityManager->flush();

        return [
            'name'          => $invoiceEntry->getName(),
            'jiraId'        => $invoiceEntry->getInvoice()->getProject()->getJiraId(),
            'invoiceId'     => $invoiceEntry->getInvoice()->getId(),
            'description'   => $invoiceEntry->getDescription(),
            'account'       => $invoiceEntry->getAccount(),
            'product'       => $invoiceEntry->getProduct()
        ];
    }

    /**
     * Delete specific invoice entry referenced by the given id
     * @param invoiceEntryId
     */
    public function deleteInvoiceEntry($invoiceEntryId)
    {
        if (empty($invoiceEntryId) || !intval($invoiceEntryId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryId]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'InvoiceEntry with id ' . $invoiceEntryId . ' did not exist');
        }

        $this->entityManager->remove($invoiceEntry);
        $this->entityManager->flush();
    }

    /**
     * Get specific project by Jira project ID
     *
     * @param $jira_id
     * @return array
     */
    public function getProject($jiraProjectId)
    {
        if (!intval($jiraProjectId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        try {
            $result = $this->jiraService->get('/rest/api/3/project/' . $jiraProjectId);
        }
        catch (HttpException $e) {
            throw $e;
        }

        $repository = $this->entityManager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $jiraProjectId]);

        if (!$project) {
            $project = new Project();
        }

        $project->setJiraId($result->id);
        $project->setJiraKey($result->key);
        $project->setName($result->name);
        $project->setUrl($result->self);
        $avatarUrls = $result->avatarUrls;
        // @TODO: cleanup decode + encode
        $avatarUrlsArr = json_decode(json_encode($avatarUrls, TRUE), TRUE);
        $avatarUrl = $avatarUrlsArr['48x48'];
        $project->setAvatarUrl($avatarUrl);

        $this->entityManager->persist($project);
        $this->entityManager->flush();

        return [
            'jiraId'    => $result->id,
            'jiraKey'   => $result->key,
            'name'      => $result->name,
            'url'       => $result->self,
            'avatarUrl' => $avatarUrl
        ];
    }

    /**
     * Get jiraIssues for project
     * @param $jira_id
     * @return array
     */
    public function getJiraIssues($jiraProjectId) {
        $jiraIssues = [];

        if (!intval($jiraProjectId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $jiraProjectId]);

        if (!$project) {
            throw new HttpException(404, 'Project with id ' . $jiraProjectId . ' not found');
        }

        $start = 0;

        while (true) {
            try {
                $results = $this->jiraService->get('rest/api/3/search?jql=project=' . $jiraProjectId . '&maxResults=1000&startAt=' . $start);
            }
            catch (HttpException $e) {
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
                $jiraIssue->setJiraUsers([$jiraIssueResult->fields->assignee->key]);
                $jiraIssues[] = ['issue_id'     => $jiraIssue->getIssueId(),
                    'summary'      => $jiraIssue->getSummary(),
                    'created'      => $jiraIssue->getCreated(),
                    'finished'     => $jiraIssue->getFinished(),
                    'jira_users'   => $jiraIssue->getJiraUsers(),
                    'time_spent'   => $jiraIssue->getTimeSpent(),
                    'project_id'   => $jiraIssue->getProject()->getId()];

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
     * Get specific customer by id
     * @param customerId
     * @return array
     */
    public function getCustomer($customerId) {
        if (!intval($customerId)) {
            throw new HttpException(400, 'Expected integer in request');
        }

        $repository = $this->entityManager->getRepository(Customer::class);
        $customer = $repository->findOneBy(['id' => $customerId]);

        if (!$customer) {
            throw new HttpException(404, 'Customer with id ' . $customerId . ' not found');
        }

        return [
            'name'   => $customer->getName(),
            'att'    => $customer->getAtt(),
            'cvr'    => $customer->getCVR(),
            'ean'    => $customer->getEAN(),
            'debtor' => $customer->getDebtor()
        ];
    }

    /**
     * Post new customer, creating a new entity referenced by the returned id
     * @return array customerData
     */
    public function postCustomer($customerData)
    {
        if (empty($customerData['name'])) {
            throw new HttpException(400, "Expected 'name' in request");
        }

        else if (empty($customerData['att'])) {
            throw new HttpException(400, "Expected 'att' in request");
        }

        else if (empty($customerData['cvr'])) {
            throw new HttpException(400, "Expected 'cvr' in request");
        }

        else if (empty($customerData['ean'])) {
            throw new HttpException(400, "Expected 'ean' in request");
        }

        else if (empty($customerData['debtor'])) {
            throw new HttpException(400, "Expected 'debtor' in request");
        }

        $customer = new Customer();
        $customer->setName($customerData['name']);
        $customer->setAtt($customerData['att']);
        $customer->setCVR($customerData['cvr']);
        $customer->setEAN($customerData['ean']);
        $customer->setDebtor($customerData['debtor']);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return [
            'customerId' => $customer->getId(),
            'name'       => $customer->getName(),
            'att'        => $customer->getAtt(),
            'cvr'        => $customer->getCVR(),
            'ean'        => $customer->getEAN(),
            'debtor'     => $customer->getDebtor()
        ];
    }

}
