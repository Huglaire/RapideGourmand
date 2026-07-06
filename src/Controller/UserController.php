<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\UserRepository;

final class UserController extends AbstractController
{
    #[Route('/api/me', name: 'app_user_me', methods: ['GET'])]
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
                'message' => 'Compte utilisateur désactivé.'
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
    public function updateMe(
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): Response
    {
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

        // Si un nouvel e-mail est renseigné
        if (isset($data['email'])) {

            // Vérifie que l'e-mail n'est pas déjà utilisé
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

        // Met à jour la date de modification.
        $user->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Informations mises à jour avec succès.'
        ], Response::HTTP_OK);

    }

    #[Route('/api/me', name: 'app_user_delete_me', methods: ['DELETE'])]
    public function deleteMe(
        EntityManagerInterface $entityManager
    ): Response
    {
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