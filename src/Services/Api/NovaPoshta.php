<?php

declare(strict_types = 1);

namespace App\Services\Api;

use GuzzleHttp\Client;

class NovaPoshta
{
    /** @var string */
    private $apiKey;

    /** @var string */
    private $endpoint = 'https://api.novaposhta.ua/v2.0/json/';

    /** @var Client */
    private $client;

    /**
     * New Post apiKey
     * @param string $apiKey
     * @param Client $client
     */
    public function __construct(string $apiKey, Client $client)
    {
        $this->client = $client;
        $this->apiKey = $apiKey;
    }

    /**
     * @param string $model
     * @param string $method
     * @param array $params
     * @return array|bool
     */
    public function request(string $model, string $method, $params = [])
    {
        $data = [
            'modelName' => $model,
            'calledMethod' => $method,
            'apiKey' => $this->apiKey,
        ];

        if (!empty($params)) {
            foreach ($params as $key => $property) {
                $data['methodProperties'][$key] = $property;
            }
        }

        $response = $this->client->post($this->endpoint, ['body' => json_encode($data)]);

        return json_decode($response->getBody()->getContents(), true, 512, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array
     */
    public function getCities(): array
    {
        return $this->request('Address', 'getCities');
    }

    /**
     * @param string $ref
     * @return array
     */
    public function getWarehouses(string $ref = null): array
    {
        $params = $ref == null ? null : ['SettlementRef' => $ref];
        return $this->request('Address', 'getWarehouses', $params);
    }
}
