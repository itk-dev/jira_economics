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
use GuzzleHttp\Exception\RequestException;

class OwnCloudService
{
    protected $client;
    protected $host;
    protected $username;
    protected $password;

    /**
     * Constructor.
     */
    public function __construct($host, $username, $password, $config = [])
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get from Own Cloud.
     *
     * @param $path
     *
     * @return mixed
     */
    public function get($path)
    {
        $client = new Client(
            [
                'base_uri' => $this->host,
            ]
        );
        try {
            $response = $client->get($path, ['auth' => [$this->username, $this->password]]);

            if ($body = $response->getBody()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }
}
