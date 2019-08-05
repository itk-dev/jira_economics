<?php

/*
 * This file is part of aakb/jira_economics.
 *
 * (c) 2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use JiraRestApi\Configuration\ArrayConfiguration;

class JiraConfiguration extends ArrayConfiguration
{
    /** @var \Symfony\Component\Security\Core\Authentication\Token\TokenInterface|null */
    protected $token;

    /** @var array|null */
    protected $authorizationConfiguration;

    public function getAuthorizationHeader($context)
    {
        if ($this->token instanceof OAuthToken) {
            $oauth1 = new Oauth1([
                'consumer_key' => $this->authorizationConfiguration['customerKey'] ?? null,
                'private_key_file' => $this->authorizationConfiguration['pemPath'] ?? null,
                'private_key_passphrase' => '',
                'signature_method' => Oauth1::SIGNATURE_METHOD_RSA,
                'token' => $this->token->getAccessToken(),
                'token_secret' => $this->token->getAccessToken(),
            ]);

            $request = new Request('GET', $this->authorizationConfiguration['jiraUrl'].$context ?? null);
            $handler = $oauth1->__invoke(function (Request $req, array $options) use (&$request) {
                $request = $req;
            });
            $handler($request, ['auth' => 'oauth']);

            if ($request->hasHeader('authorization')) {
                $authorization = $request->getHeader('authorization');

                return reset($authorization);
            }
        }

        return null;
    }
}
