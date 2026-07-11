<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\UserRepository;
use OpenApi\Attributes as OA;

final class UserController extends AbstractController
{
    #[Route('/api/me', name: 'app_user_me', methods: ['GET'])]
    #[OA\Get(
        path: '/api/me',
        summary: 'Consulter son profil',
        description: 'Retourne les informations du profil de l\'utilisateur connecté.',
        tags: ['Utilisateurs'],
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Profil utilisateur récupéré avec succès.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'id', type: 'integer', example: 1),
                        new OA\Property(property: 'email', type: 'string', example: 'john.doe@example.com'),
                        new OA\Property(
                            property: 'roles',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                            example: ['ROLE_USER']
                        ),
                        new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                        new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                        new OA\Property(property: 'phone', type: 'string', example: '0612345678'),
                        new OA\Property(property: 'street', type: 'string', example: '37 rue de la Gourde Bleue'),
                        new OA\Property(property: 'postalCode', type: 'string', example: '33000'),
                        new OA\Property(property: 'city', type: 'string', example: 'Bordeaux')
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Utilisateur non authentifié.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Utilisateur non authentifié.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Compte utilisateur déjà désactivé.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Compte utilisateur déjà désactivé.'
                        )
                    ]
                )
            )
        ]
    )]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non authentifié.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isActive()) {
            return $this->json([
                'message' => 'Compte utilisateur déjà désactivé.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'phone' => $user->getPhone(),
            'street' => $user->getStreet(),
            'postalCode' => $user->getPostalCode(),
            'city' => $user->getCity(),
        ]);
    }


    #[Route('/api/me', name: 'app_user_update_me', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/me',
        summary: 'Mettre à jour son profil',
        description: 'Met à jour les informations de l’utilisateur authentifié.',
        tags: ['Utilisateurs'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'john.doe@example.com'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'John'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Doe'),
                    new OA\Property(property: 'phone', type: 'string', example: '0612345678'),
                    new OA\Property(property: 'street', type: 'string', example: '37 rue de la Gourde Bleue'),
                    new OA\Property(property: 'postalCode', type: 'string', example: '33000'),
                    new OA\Property(property: 'city', type: 'string', example: 'Bordeaux')
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Informations mises à jour avec succès.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Informations mises à jour avec succès.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Utilisateur non authentifié.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Utilisateur non authentifié.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Compte utilisateur désactivé.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Compte utilisateur désactivé.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 409,
                description: 'Adresse e-mail déjà utilisée.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Cet email est déjà utilisé.'
                        )
                    ]
                )
            )
        ]
    )]
    public function updateMe(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non authentifié.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isActive()) {
            return $this->json([
                'message' => 'Compte utilisateur désactivé.'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = $request->toArray();

        if (isset($data['firstName'])) {
            $user->setFirstName($data['firstName']);
        }

        if (isset($data['lastName'])) {
            $user->setLastName($data['lastName']);
        }

        if (isset($data['phone'])) {
            $user->setPhone($data['phone']);
        }

        if (isset($data['street'])) {
            $user->setStreet($data['street']);
        }

        if (isset($data['postalCode'])) {
            $user->setPostalCode($data['postalCode']);
        }

        if (isset($data['city'])) {
            $user->setCity($data['city']);
        }

        if (isset($data['email'])) {

            if ($data['email'] !== $user->getEmail()) {

                $existingUser = $userRepository->findOneBy([
                    'email' => $data['email']
                ]);

                if ($existingUser !== null) {
                    return $this->json([
                        'message' => 'Cet email est déjà utilisé.'
                    ], Response::HTTP_CONFLICT);
                }

                $user->setEmail($data['email']);
            }
        }

        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Informations mises à jour avec succès.'
        ], Response::HTTP_OK);
    }

    #[Route('/api/me', name: 'app_user_delete_me', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/me',
        summary: 'Désactiver son compte',
        description: 'Désactive le compte de l’utilisateur authentifié sans supprimer ses données.',
        tags: ['Utilisateurs'],
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Compte désactivé avec succès.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Compte désactivé avec succès.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Utilisateur non authentifié.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Utilisateur non authentifié.'
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: 'Compte utilisateur désactivé.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Compte utilisateur désactivé.'
                        )
                    ]
                )
            )
        ]
    )]
    public function deleteMe(
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'Utilisateur non authentifié.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!$user->isActive()) {
            return $this->json([
                'message' => 'Compte utilisateur désactivé.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Désactive le compte utilisateur (plus sécuritaire qu'une suppression brute pour garder l'historique des commandes)
        $user->setIsActive(false);

        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Compte désactivé avec succès.'
        ], Response::HTTP_OK);
    }
}
