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

        return ['name'   => $invoice->getName(),
                'jiraId' => $invoice->getProject()->getJiraId()];
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