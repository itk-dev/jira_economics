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
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Serializer;

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
            $response = $client->get(
                $path,
                [
                    'auth' => [$this->username, $this->password],
                ]
            );

            if ($body = $response->getBody()->getContents()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * Post to OwnCloudService.
     *
     * @param $path
     *
     * @return mixed
     */
    public function post($path, $data)
    {
        $client = new Client(
            [
            'base_uri' => $this->host,
            ]
        );

        // Set the "auth" request option to "oauth" to sign using oauth
        try {
            $response = $client->post(
                $path,
                [
                'auth' => [$this->username, $this->password],
                'json' => $data,
                ]
            );

            if ($body = $response->getBody()->getContents()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * Create new folder in OwnCloud.
     *
     * @param $path
     *  The path of the folder
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function mkCol($path)
    {
        $client = new Client(
            [
            'base_uri' => $this->host,
            ]
        );

        // Set the "auth" request option to "oauth" to sign using oauth
        try {
            $response = $client->request(
                'MKCOL',
                $path,
                [
                'auth' => [$this->username, $this->password],
                ]
            );

            if ($body = $response->getBody()->getContents()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }

    /**
     * Get properties of a folder.
     *
     * @param $path
     *  The path of the folder
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function propFind($path)
    {

      $encoders = [new XmlEncoder()];
      $normalizers = [];

      $serializer = new Serializer($normalizers, $encoders);
      $client = new Client(
        [
          'base_uri' => $this->host,
        ]
      );

      // Set the "auth" request option to "oauth" to sign using oauth
      try {
        $response = $client->request(
          'PROPFIND',
          $path,
          [
            'auth' => [$this->username, $this->password],
          ]
        );

        if ($body = $response->getBody()->getContents()) {
          $folders = [];
          $content = $serializer->decode($body, 'xml');
          foreach ($content['d:response'] as $folder) {
            if (is_array($folder) && array_key_exists('d:href', $folder)) {
              $folders[] = str_replace($path, '', $folder['d:href']);
            }
          }
          return $folders;
        }
      } catch (RequestException $e) {
        throw $e;
      }
    }

    /**
     * Upload a file to owncloud.
     *
     * @param $path
     *
     * @return mixed
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function sendFile($path, $file)
    {
        $client = new Client(
            [
            'base_uri' => $this->host,
            ]
        );

        // Set the "auth" request option to "oauth" to sign using oauth
        try {
            $response = $client->put(
                $path,
                [
                'auth' => [$this->username, $this->password],
                'body' => $file,
                ]
            );

            if ($body = $response->getBody()) {
                return json_decode($body);
            }
        } catch (RequestException $e) {
            throw $e;
        }
    }
}
