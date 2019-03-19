<?php

namespace App\Service;

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
     * Get specific project by Jira project ID
     *
     * @param $jira_id
     * @return array
     */
    public function getProject($jiraProjectId)
    {
        $project_to_return = [];

        try {
            $result = $this->get('/rest/api/3/project/' . $jiraProjectId);
        }
        catch (HttpException $e) {
            throw $e;
        }

        $repository = $this->entity_manager->getRepository(Project::class);
        $project = $repository->findOneBy(['jira_id' => $jiraProjectId]);

        if (!$project) {
            $project = new Project();
        }

        //TODO cleanup redundancy

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

        $project_to_return = ['jira_id'     => $result->id,
                              'jira_key'    => $result->key,
                              'name'        => $result->name,
                              'url'         => $result->self,
                              'avatar_url'  => $avatarUrl];

        return $project_to_return;
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
            $result = $this->get('/rest/api/3/project/search?startAt='.$start);
            $projects = array_merge($projects, array_map(function ($res) {
                $res->url = parse_url($res->self, PHP_URL_SCHEME) . '://' . parse_url($res->self, PHP_URL_HOST) . '/browse/' . $res->key;

                return $res;
            }, $result->values));

            if ($result->isLast) {
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