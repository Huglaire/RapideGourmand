<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    #[OA\Post(
        path: '/api/register',
        summary: 'Créer un compte utilisateur',
        description: 'Crée un nouvel utilisateur avec le rôle ROLE_USER.',
        tags: ['Authentification'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'email',
                    'password',
                    'firstName',
                    'lastName',
                    'phone',
                    'street',
                    'postalCode',
                    'city'
                ],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        example: 'john.doe@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'password'
                    ),
                    new OA\Property(
                        property: 'firstName',
                        type: 'string',
                        example: 'John'
                    ),
                    new OA\Property(
                        property: 'lastName',
                        type: 'string',
                        example: 'Doe'
                    ),
                    new OA\Property(
                        property: 'phone',
                        type: 'string',
                        example: '0612345678'
                    ),
                    new OA\Property(
                        property: 'street',
                        type: 'string',
                        example: '37 rue de la Gourde Bleue'
                    ),
                    new OA\Property(
                        property: 'postalCode',
                        type: 'string',
                        example: '33000'
                    ),
                    new OA\Property(
                        property: 'city',
                        type: 'string',
                        example: 'Bordeaux'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Utilisateur créé avec succès.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Utilisateur créé avec succès.'
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
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {
        $data = $request->toArray();

        $existingUser = $userRepository->findOneBy([
            'email' => $data['email']
        ]);

        if ($existingUser !== null) {
            return $this->json([
                'message' => 'Cet email est déjà utilisé.'
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();

        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setPassword(
            $passwordHasher->hashPassword($user, $data['password'])
        );
        $user->setPhone($data['phone']);
        $user->setStreet($data['street']);
        $user->setPostalCode($data['postalCode']);
        $user->setCity($data['city']);
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json([
            'message' => 'Utilisateur créé avec succès.'
        ], Response::HTTP_CREATED);
    }
}