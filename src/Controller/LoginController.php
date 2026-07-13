<?php

namespace App\Controller;

use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

final class LoginController extends AbstractController
{
    #[Route('/api/login_check', name: 'app_login_documentation', methods: ['POST'])]
    #[OA\Post(
        path: '/api/login_check',
        summary: 'Se connecter',
        description: 'Authentifie un utilisateur et retourne un jeton JWT.',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        example: 'miles.davis@mail.fr'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'password'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authentification réussie.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'token',
                            type: 'string',
                            example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Identifiants invalides.'
            )
        ]
    )]
    public function login(): void
    {
        throw new \LogicException(
            'Cette méthode est interceptée par le firewall JWT.'
        );
    }
}