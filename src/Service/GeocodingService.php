<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class GeocodingService
{
    /**
     * URL de l'API publique OpenStreetMap Nominatim.
     */
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';

    public function __construct(
        private readonly HttpClientInterface $httpClient
    ) {
    }

    /**
     * Convertit une adresse postale en coordonnées GPS.
     *
     * @throws \RuntimeException Si l'adresse est introuvable.
     */
    public function geocode(
        string $street,
        string $postalCode,
        string $city
    ): array {

        // Construction de l'adresse complète transmise à l'API.
        $address = sprintf(
            '%s, %s %s, France',
            $street,
            $postalCode,
            $city
        );

        // Envoi de la requête HTTP vers l'API Nominatim.
        $response = $this->httpClient->request(
            'GET',
            self::NOMINATIM_URL,
            [
                'query' => [
                    'q' => $address,
                    'format' => 'jsonv2',
                    'limit' => 1,
                ],

                // L'API Nominatim impose la présence d'un User-Agent.
                'headers' => [
                    'User-Agent' => 'RapideGourmand/1.0',
                ],

                'timeout' => 10,
            ]
        );

        // Conversion de la réponse JSON en tableau PHP.
        $results = $response->toArray(false);

        // Vérification qu'une adresse correspondante a été trouvée.
        if (empty($results)) {
            throw new \RuntimeException(
                'Adresse introuvable.'
            );
        }

        // Retour des coordonnées GPS de la première adresse trouvée.
        return [
            'lat' => (float) $results[0]['lat'],
            'lon' => (float) $results[0]['lon'],
        ];
    }
}