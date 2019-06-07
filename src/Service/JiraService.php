<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * Post to Jira.
     *
     * @param $path
     * @return mixed
     */
    public function post($path, $data)
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
            $response = $client->post($path,
              [
                'auth' => 'oauth',
                'json' => $data
            ]);

            if ($body = $response->getBody()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * Post to Jira.
     *
     * @param $path
     * @return mixed
     */
    public function put($path, $data)
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
            $response = $client->put($path,
              [
                'auth' => 'oauth',
                'json' => $data
              ]);

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
     * Get project.
     *
     * @param $key
     *   A project key or id.
     *
     * @return array
     */
    public function getProject($key)
    {
        $project = $this->get('/rest/api/2/project/' . $key);

        return $project;
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
     * Get all projects, including archived.
     *
     * @return array
     */
    public function getAllProjects()
    {
        $projects = $this->get('/rest/api/2/project');

        return $projects;
    }

    /**
     * Get project categories.
     *
     * @return array
     */
    public function getAllProjectCategories()
    {
        $projectCategories = $this->get('/rest/api/2/projectCategory');

        return $projectCategories;
    }

    /**
     * Get all accounts.
     *
     * @return array
     */
    public function getAllAccounts()
    {
        $accounts = $this->get('/rest/tempo-accounts/1/account/');

        return $accounts;
    }

    /**
     * Get all accounts.
     *
     * @return array
     */
    public function getAllCustomers()
    {
        $accounts = $this->get('/rest/tempo-accounts/1/customer/');

        return $accounts;
    }

    /**
     * Get current user.
     *
     * @return mixed
     */
    public function getCurrentUser() {
        $result = $this->get('/rest/api/2/myself');

        return $result;
    }
}
