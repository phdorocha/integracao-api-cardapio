<?php

namespace PauloAndrade\IntegracaoAPICardapio;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Message;

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
            return $e;
        } catch (\Exception $e) {
            $response = $e->getMessage();
            return ['error' => $response];
        }
    }
    public function setToken(string $token)
    {
        $this->token = $token;
    }

    public function updateExpiration($newDate)
    {
        $options = $this->optionsRequest;
        $options['headers']['Authorization'] = "Bearer {$this->token}";
        $options['expiracao'] = $newDate;

        try {
            $response = $this->client->request(
                'PUT',
                "/config/expiration",
                $options
            );

            $statusCode = $response->getStatusCode();
            $result = json_decode($response->getBody()->getContents());
            return array('status' => $statusCode, 'response' => $result);
        } catch (ClientException $e) {
            return $e->getMessage();
        } catch (\Exception $e) {
            $response = $e->getMessage();
            return ['error' => "Falha ao atulizar o expiration: {$response}"];
        }
    }
}
