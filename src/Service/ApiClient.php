<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiClient
{
    private string $apiBaseUrl;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        string $apiBaseUrl,
    ) {
        $this->apiBaseUrl = rtrim($apiBaseUrl, '/');
    }

    /**
     * Effectue une requête GET vers l'API.
     */
    public function get(string $endpoint): array
    {
        $response = $this->httpClient->request(
            'GET',
            $this->apiBaseUrl . '/' . ltrim($endpoint, '/')
        );

        return $response->toArray();
    }
}