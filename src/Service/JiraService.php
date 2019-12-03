<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class JiraService extends AbstractJiraService
{
    protected $tokenStorage;
    protected $customerKey;
    protected $pemPath;

    /**
     * Constructor.
     *
     * @param $jiraUrl
     * @param $tokenStorage
     * @param $customerKey
     * @param $pemPath
     * @param $customFieldMappings
     */
    public function __construct(
        $jiraUrl,
        $tokenStorage,
        $customerKey,
        $pemPath,
        $customFieldMappings
    ) {
        parent::__construct($jiraUrl, $customFieldMappings);
        $this->tokenStorage = $tokenStorage;
        $this->customerKey = $customerKey;
        $this->pemPath = $pemPath;
        $this->jiraUrl = $jiraUrl;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient()
    {
        $stack = HandlerStack::create();
        $token = $this->tokenStorage->getToken();

        if ($token instanceof AnonymousToken) {
            throw new HttpException(401, 'unauthorized');
        }

        $middleware = $this->setOauth($token);

        $stack->push($middleware);

        return new Client(
            [
                'base_uri' => $this->jiraUrl,
                'handler' => $stack,
                'auth' => 'oauth',
            ]
        );
    }

    /**
     * Set OAuth token.
     *
     * @param $token
     *
     * @return \GuzzleHttp\Subscriber\Oauth\Oauth1
     */
    private function setOauth($token)
    {
        $accessToken = null;
        $accessTokenSecret = null;

        if (!$token instanceof AnonymousToken) {
            $accessToken = $token->getAccessToken();
            $accessTokenSecret = $token->getTokenSecret();
        }

        $middleware = new Oauth1(
            [
                'consumer_key' => $this->customerKey,
                'private_key_file' => $this->pemPath,
                'private_key_passphrase' => '',
                'signature_method' => Oauth1::SIGNATURE_METHOD_RSA,
                'token' => $accessToken,
                'token_secret' => $accessTokenSecret,
            ]
        );

        return $middleware;
    }

    /**
     * Get current user.
     *
     * @return mixed
     */
    public function getCurrentUser()
    {
        $result = $this->get('/rest/api/2/myself');

        return $result;
    }

    /**
     * Get current user permissions.
     *
     * @return mixed
     */
    public function getCurrentUserPermissions()
    {
        $result = $this->get('/rest/api/2/mypermissions');

        return $result;
    }

    /**
     * Get list of allowed permissions for current user.
     *
     * @return array
     */
    public function getPermissionsList()
    {
        $list = [];
        $rest_permissions = $this->getCurrentUserPermissions();
        if (property_exists($rest_permissions, 'permissions')) {
            foreach ($rest_permissions->permissions as $permission_name => $value) {
                if (property_exists($value, 'havePermission') && 1 === $value->havePermission) {
                    $list[] = $permission_name;
                }
            }
        }

        return $list;
    }
}
