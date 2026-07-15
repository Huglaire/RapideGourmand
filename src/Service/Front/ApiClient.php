<?php

namespace App\Service\Front;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {}

    /**
     * Effectue une requête HTTP vers l'API.
     */
    public function request(
        string $method,
        string $url,
        array $options = []
    ): ResponseInterface {
        return $this->httpClient->request(
            $method,
            'http://127.0.0.1:8000' . $url,
            $options
        );
    }
}
