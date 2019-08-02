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
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class JiraService extends AbstractJiraService
{
    protected $tokenStorage;
    protected $customerKey;
    protected $pemPath;

    /**
     * Constructor.
     */
    public function __construct(
        $jiraUrl,
        $tokenStorage,
        $customerKey,
        $pemPath
    ) {
        parent::__construct($jiraUrl);
        $this->tokenStorage = $tokenStorage;
        $this->customerKey = $customerKey;
        $this->pemPath = $pemPath;
        $this->jiraUrl = $jiraUrl;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(string $path = '')
    {
        $stack = HandlerStack::create();
        $token = $this->tokenStorage->getToken();

        if ($token instanceof AnonymousToken) {
            throw new HttpException(401, 'unauthorized');
        }

        $headers = [];

        if ($token instanceof OAuthToken) {
            $oauth1 = new Oauth1(
                [
                    'consumer_key' => $this->customerKey,
                    'private_key_file' => $this->pemPath,
                    'private_key_passphrase' => '',
                    'signature_method' => Oauth1::SIGNATURE_METHOD_RSA,
                    'token' => $token->getAccessToken(),
                    'token_secret' => $token->getAccessToken(),
                ]
            );

            $request = new Request('GET', $this->jiraUrl.$path);
            $handler = $oauth1->__invoke(function (Request $req, array $options) use (&$request) {
                $request = $req;
            });
            $handler($request, ['auth' => 'oauth']);

            if ($request->hasHeader('authorization')) {
                $headers['authorization'] = $request->getHeader('authorization');
            }
        }

        return new Client([
            'base_uri' => $this->jiraUrl,
            'headers' => $headers,
        ]);
    }
}
