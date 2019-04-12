<?php

namespace App\Service;

use App\Entity\Invoice;
use App\Entity\InvoiceEntry;
use App\Entity\Project;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;

class JiraService
{
    protected $token_storage;
    protected $customer_key;
    protected $pem_path;
    protected $jira_url;
    private $entity_manager;

    /**
     * Constructor.
     */
    public function __construct(
        $token_storage,
        $customer_key,
        $pem_path,
        $jira_url,
        EntityManagerInterface $entity_manager
    ) {
        $this->token_storage = $token_storage;
        $this->customer_key = $customer_key;
        $this->pem_path = $pem_path;
        $this->jira_url = $jira_url;
        $this->entity_manager = $entity_manager;
    }

    /**
     * Get from Jira.
     *
     * @param $path
     * @return mixed
     */
    public function get($path)
    {
        $stack = HandlerStack::create();
        $token = $this->token_storage->getToken();

        if ($token instanceof AnonymousToken) {
            throw new HttpException(401, 'unauthorized');
        }

        $middleware = $this->setOauth($token);

        $stack->push($middleware);

        $client = new Client(
            [
                'base_uri' => $this->jira_url,
                'handler' => $stack,
            ]
        );

        // Set the "auth" request option to "oauth" to sign using oauth
        try {
            $response = $client->get($path, ['auth' => 'oauth']);

            if ($body = $response->getBody()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * Set OAuth token
     *
     * @param $token
     * @return \GuzzleHttp\Subscriber\Oauth\Oauth1
     */
    public function setOauth($token)
    {
        $accessToken = null;
        $accessTokenSecret = null;

        if (!$token instanceof AnonymousToken) {
            $accessToken = $token->getAccessToken();
            $accessTokenSecret = $token->getTokenSecret();
        }

        $middleware = new Oauth1(
            [
                'consumer_key' => $this->customer_key,
                'private_key_file' => $this->pem_path,
                'private_key_passphrase' => '',
                'signature_method' => Oauth1::SIGNATURE_METHOD_RSA,
                'token' => $accessToken,
                'token_secret' => $accessTokenSecret,
            ]
        );

        return $middleware;
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

        $repository = $this->entity_manager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $jiraProjectId]);

        if (!$project) {
            throw new HttpException(404, 'Project with id ' . $jiraProjectId . ' not found');
        }

        $invoices = $project->getInvoices();

        $invoicesJson = [];

        foreach ($invoices AS $invoice) {
            $invoicesJson[] = ['id'   => $invoice->getId(),
                               'name' => $invoice->getName()];
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

        $repository = $this->entity_manager->getRepository(Invoice::class);
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

        return ['name'   => $invoice->getName(),
                'jiraId' => $invoice->getProject()->getJiraId(),
                'recorded' => $recorded,
                'created' => $invoice->getCreated()];
    }

    /**
     * Post new invoice, creating a new entity referenced by the returned id
     * @return invoiceData
     * @return array
     */
    public function postInvoice($invoiceData)
    {
        if (empty($invoiceData['projectId']) || !intval($invoiceData['projectId'])) {
            throw new HttpException(400, "Expected integer value for 'projectId' in request");
        }

        if (empty($invoiceData['name'])) {
            throw new HttpException(400, "Expected 'name' in request");
        }

        $repository = $this->entity_manager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $invoiceData['projectId']]);

        if (!$project) {
            throw new HttpException(400, "Project with id " . $invoiceData['projectId'] . " not found");
        }

        $invoice = new Invoice();
        $invoice->setName($invoiceData['name']);
        $invoice->setProject($project);
        $invoice->setRecorded(false);
        $invoice->setCreated(new \DateTime("now"));

        $this->entity_manager->persist($invoice);
        $this->entity_manager->flush();

        return ['invoiceId' => $invoice->getId(),
                'name'      => $invoice->getName(),
                'jiraId'    => $invoice->getProject()->getJiraId(),
                'recorded'  => $invoice->getRecorded(),
                'created'   => $invoice->getCreated()];
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

        $repository = $this->entity_manager->getRepository(Invoice::class);
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

        $this->entity_manager->persist($invoice);
        $this->entity_manager->flush();

        return ['name'      => $invoice->getName(),
                'jiraId'    => $invoice->getProject()->getJiraId(),
                'recorded'  => $invoice->getRecorded(),
                'created'   => $invoice->getCreated()];
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

        $repository = $this->entity_manager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceId]);

        $invoiceEntries = $invoice->getInvoiceEntries();

        $invoiceEntriesJson = [];

        foreach ($invoiceEntries AS $invoiceEntry) {
            $invoiceEntriesJson[] = ['id'   => $invoiceEntry->getId(),
                                     'name' => $invoiceEntry->getName()];
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

        $repository = $this->entity_manager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryId]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'InvoiceEntry with id ' . $invoiceEntryId . ' not found');
        }

        return ['id'    => $invoiceEntry->getId(),
                'name'  => $invoiceEntry->getName()];
    }

    /**
     * Post new invoiceEntry, creating a new entity referenced by the returned id
     * @return invoiceEntryData
     * @return array
     */
    public function postInvoiceEntry($invoiceEntryData)
    {
        if (empty($invoiceEntryData['invoiceId']) || !intval($invoiceEntryData['invoiceId'])) {
            throw new HttpException(400, "Expected integer value for 'invoiceId' in request");
        }

        if (empty($invoiceEntryData['name'])) {
            throw new HttpException(400, "Missing 'name' for new invoice entry in request");
        }

        $repository = $this->entity_manager->getRepository(Invoice::class);
        $invoice = $repository->findOneBy(['id' => $invoiceEntryData['invoiceId']]);

        if (!$invoice) {
            throw new HttpException(400, "Invoice with id " . $invoiceEntryData['invoiceId'] . " not found");
        }

        $invoiceEntry = new InvoiceEntry();
        $invoiceEntry->setName($invoiceEntryData['name']);
        $invoiceEntry->setInvoice($invoice);

        $this->entity_manager->persist($invoiceEntry);
        $this->entity_manager->flush();

        return ['invoiceEntryId'    => $invoiceEntry->getId(),
                'name'              => $invoiceEntry->getName(),
                'jiraId'            => $invoiceEntry->getInvoice()->getProject()->getJiraId(),
                'invoiceId'         => $invoiceEntry->getInvoice()->getId()];
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

        $repository = $this->entity_manager->getRepository(InvoiceEntry::class);
        $invoiceEntry = $repository->findOneBy(['id' => $invoiceEntryData['id']]);

        if (!$invoiceEntry) {
            throw new HttpException(404, 'Unable to update invoiceEntry with id ' . $invoiceEntryData['id'] . ' as it does not already exist');
        }

        if (!empty($invoiceEntryData['name'])) {
            $invoiceEntry->setName($invoiceEntryData['name']);
        }

        $this->entity_manager->persist($invoiceEntry);
        $this->entity_manager->flush();

        return ['name'      => $invoiceEntry->getName(),
                'jiraId'    => $invoiceEntry->getInvoice()->getProject()->getJiraId(),
                'invoiceId' => $invoiceEntry->getInvoice()->getId()];
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
            $result = $this->get('/rest/api/3/project/' . $jiraProjectId);
        }
        catch (HttpException $e) {
            throw $e;
        }

        $repository = $this->entity_manager->getRepository(Project::class);
        $project = $repository->findOneBy(['jiraId' => $jiraProjectId]);

        if (!$project) {
            $project = new Project();
        }

        $project->setJiraId($result->id);
        $project->setJiraKey($result->key);
        $project->setName($result->name);
        $project->setUrl($result->self);
        $avatarUrls = $result->avatarUrls;
        $avatarUrlsArr = json_decode(json_encode($avatarUrls, TRUE), TRUE);
        $avatarUrl = $avatarUrlsArr['48x48'];
        $project->setAvatarUrl($avatarUrl);

        $this->entity_manager->persist($project);
        $this->entity_manager->flush();

        return ['jiraId'    => $result->id,
                'jiraKey'   => $result->key,
                'name'      => $result->name,
                'url'       => $result->self,
                'avatarUrl' => $avatarUrl];
    }

    /**
     * Get all projects.
     *
     * @return array
     */
    public function getProjects()
    {
        $projects = [];

        $start = 0;
        while (true) {
            $results = $this->get('/rest/api/3/project/search?startAt='.$start);
            foreach ($results->values as $result) {
                if (!isset($result->projectCategory) || $result->projectCategory->name != 'Lukket') {
                    $result->url = parse_url($result->self, PHP_URL_SCHEME) . '://' . parse_url($result->self, PHP_URL_HOST) . '/browse/' . $result->key;

                    $projects[] = $result;
                }
            }

            if ($results->isLast) {
                break;
            }

            $start = $start + 50;
        }

        return $projects;
    }

    /**
     * Get current user.
     *
     * @return mixed
     */
    public function getCurrentUser() {
        $result = $this->get('/rest/api/3/myself');

        return $result;
    }

}