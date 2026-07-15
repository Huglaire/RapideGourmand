<?php

namespace App\Service\Front;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class AuthService
{
    public function __construct(
        private readonly ApiClient $apiClient,
    ) {}

    /**
     * Authentifie un utilisateur via l'API.
     */
    public function login(
        string $email,
        string $password,
    ): array {
        try {
            $response = $this->apiClient->request(
                'POST',
                '/api/login_check',
                [
                    'json' => [
                        'email' => $email,
                        'password' => $password,
                    ],
                ]
            );

            $data = $response->toArray(false);

            if (isset($data['token'])) {
                return [
                    'success' => true,
                    'token' => $data['token'],
                ];
            }

            return [
                'success' => false,
                'message' => 'Adresse e-mail ou mot de passe incorrect.',
            ];
        } catch (TransportExceptionInterface) {

            return [
                'success' => false,
                'message' => 'Impossible de contacter le serveur.',
            ];
        }
    }
}
