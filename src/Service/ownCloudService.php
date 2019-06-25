<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class ownCloudService
{
    protected $client;
    protected $host;
    protected $username;
    protected $password;

    /**
     * Constructor.
     */
    public function __construct($host, $username, $password, $config = array())
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * Get from Own Cloud.
     *
     * @param $path
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


