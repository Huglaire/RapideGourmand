<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\UserRepository;

final class RegisterController extends AbstractController
{
    #[Route('/api/register', name: 'app_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response
    {
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