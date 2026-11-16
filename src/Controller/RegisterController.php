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
                        example: 'Password1!'
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
                description: 'Utilisateur créé avec succès.'
            ),
            new OA\Response(
                response: 400,
                description: 'Adresse e-mail ou mot de passe invalide.'
            ),
            new OA\Response(
                response: 409,
                description: 'Adresse e-mail déjà utilisée.'
            )
        ]
    )]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response {

        // Récupération des données JSON envoyées par le frontend.
        $data = $request->toArray();

        // Vérification de la présence des champs obligatoires.
        $requiredFields = [
            'email',
            'password',
            'firstName',
            'lastName',
            'phone',
            'street',
            'postalCode',
            'city'
        ];

        foreach ($requiredFields as $field) {

            if (
                !isset($data[$field]) ||
                !is_string($data[$field]) ||
                trim($data[$field]) === ''
            ) {
                return $this->json([
                    'message' => 'Veuillez renseigner tous les champs obligatoires.'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Nettoyage de l'adresse e-mail.
        $email = trim($data['email']);

        // Vérification du format de l'adresse e-mail côté serveur.
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

            return $this->json([
                'message' => 'Veuillez saisir une adresse e-mail valide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérification de la robustesse du mot de passe côté serveur.
        // 8 caractères minimum, une majuscule, une minuscule,
        // un chiffre et un caractère spécial.
        $passwordRegex =
            '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/';

        if (!preg_match($passwordRegex, $data['password'])) {

            return $this->json([
                'message' => 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérification de l'unicité de l'adresse e-mail.
        $existingUser = $userRepository->findOneBy([
            'email' => $email
        ]);

        if ($existingUser !== null) {

            return $this->json([
                'message' => 'Cet email est déjà utilisé.'
            ], Response::HTTP_CONFLICT);
        }

        // Création du nouvel utilisateur.
        $user = new User();

        $user->setEmail($email);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);

        // Hachage du mot de passe avant son enregistrement.
        $user->setPassword(
            $passwordHasher->hashPassword($user, $data['password'])
        );

        $user->setPhone($data['phone']);
        $user->setStreet($data['street']);
        $user->setPostalCode($data['postalCode']);
        $user->setCity($data['city']);

        // Attribution du rôle utilisateur par défaut.
        $user->setRoles(['ROLE_USER']);

        // Enregistrement de l'utilisateur dans MySQL via Doctrine.
        $entityManager->persist($user);
        $entityManager->flush();

        // Confirmation de la création du compte.
        return $this->json([
            'message' => 'Utilisateur créé avec succès.'
        ], Response::HTTP_CREATED);
    }
}