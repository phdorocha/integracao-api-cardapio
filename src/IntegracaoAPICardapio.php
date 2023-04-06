<?php

namespace PauloAndrade\IntegracaoAPICardapio;

use DateInterval;
use DateTime;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;

class IntegracaoAPICardapio
{
    protected $token;
    protected $optionsRequest = [];
    private $client;

    function __construct()
    {
        $config = [];
        $this->client = new Client([
            'base_uri' => 'http://integracao-cardapio.sidsolucoes.com',
        ]);

        // if (isset($config['verify'])) {
        //     if ($config['verify'] == '') {
        //         $verify = false;
        //     } elseif ($config['verify'] != '' && $config['verify'] != 1) {
        //         $verify = $config['verify'];
        //     } else {
        //         $verify = $config['certificate'];
        //     }
        // } else {
        //     $verify = $config['certificate'];
        // }

        $this->optionsRequest = [
            'headers' => [
                'Accept' => 'application/json'
            ],
            // 'cert' => $config['certificate'],
            // 'verify' => $verify,
            // 'ssl_key' => $config['certificateKey'],
        ];
    }

    public function getToken($email, $password)
    {
        $options = $this->optionsRequest;
        $options['form_params'] = [
            'email' => $email,
            'password' => $password,
        ];

        try {
            $response = $this->client->request(
                'POST',
                'api/login',
                $options
            );

            return (array) json_decode($response->getBody()->getContents());
        } catch (ClientException $e) {
            return $this->parseResultClient($e);
        } catch (\Exception $e) {
            $response = $e->getMessage();
            return ['error' => $response];
        }
    }

    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function updateExpiration($newDate, $cnpj)
    {
        $this->optionsRequest = [
            'headers' => [
                'Accept' => 'application/json'
            ],
        ];
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ];
        $body = '{"expiracao": "' . $newDate . '"}';

        try {
            $request = new Request(
                'PUT',
                "api/configs/$cnpj/expiration",
                $headers,
                $body
            );
            $response = $this->client->sendAsync($request)->wait();

            $statusCode = $response->getStatusCode();
            $result = json_decode($response->getBody()->getContents());
            return array('status' => $statusCode, 'response' => $result);
        } catch (ClientException $e) {
            return $this->parseResultClient($e);
        } catch (\Exception $e) {
            $response = $e->getMessage();
            return ['error' => "Falha ao atualizar o expiration: {$response}"];
        }
    }

    /**
     * return resonse error
     */
    private function parseResultClient($result)
    {
        $statusCode = $result->getResponse()->getStatusCode();
        $response = $result->getResponse()->getReasonPhrase();
        $body = $result->getResponse()->getBody()->getContents();

        return ['error' => $body, 'response' => $response, 'statusCode' => $statusCode];
    }
}
