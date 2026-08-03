<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';

    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    /**
     * Retourne les coordonnées GPS d'une adresse.
     *
     * @throws \RuntimeException
     */
    public function geocode(
        string $street,
        string $postalCode,
        string $city
    ): array {

        $address = sprintf(
            '%s, %s %s, France',
            $street,
            $postalCode,
            $city
        );

        $response = $this->httpClient->request(
            'GET',
            self::NOMINATIM_URL,
            [
                'query' => [
                    'q' => $address,
                    'format' => 'jsonv2',
                    'limit' => 1,
                ],

                'headers' => [
                    'User-Agent' => 'RapideGourmand/1.0',
                ],

                'timeout' => 10,
            ]
        );

        $results = $response->toArray(false);

        if (empty($results)) {
            throw new \RuntimeException(
                'Adresse introuvable.'
            );
        }

        return [
            'lat' => (float) $results[0]['lat'],
            'lon' => (float) $results[0]['lon'],
        ];
    }
}