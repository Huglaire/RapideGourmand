<?php

namespace App\Service\Front;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class OrderService
{
    public function __construct(
        private readonly ApiClient $apiClient,
        private readonly RequestStack $requestStack,
    ) {}

    /**
     * Crée une commande via l'API.
     */
    public function create(array $payload): array
    {
        try {

            $session =
                $this->requestStack
                    ->getSession();

            $response = $this->apiClient->request(

                'POST',

                '/api/orders',

                [

                    'headers' => [

                        'Authorization' => sprintf(
                            'Bearer %s',
                            $session->get('jwt')
                        )

                    ],

                    'json' => $payload

                ]

            );

            return $response->toArray(false);

        } catch (TransportExceptionInterface) {

            return [

                'success' => false,

                'message' =>
                    'Impossible de contacter le serveur.'

            ];

        }

    }
}