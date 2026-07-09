<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AdminController extends AbstractController
{
    #[Route('/api/admin/employees', name: 'app_admin_create_employee', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function createEmployee(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Vérifie que le corps de la requête contient un JSON valide
        if (!is_array($data)) {
            return $this->json([
                'message' => 'Le corps de la requête est invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie que tous les champs obligatoires sont renseignés
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
            if (empty($data[$field])) {
                return $this->json([
                    'message' => 'Le champ "' . $field . '" est obligatoire.'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        // Vérifie que l'email n'est pas déjà utilisé
        if ($userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json([
                'message' => 'Cet email est déjà utilisé.'
            ], Response::HTTP_CONFLICT);
        }

        // Crée le compte employé
        $employee = new User();

        $employee->setEmail($data['email']);
        $employee->setFirstName($data['firstName']);
        $employee->setLastName($data['lastName']);
        $employee->setPassword(
            $passwordHasher->hashPassword($employee, $data['password'])
        );
        $employee->setPhone($data['phone']);
        $employee->setStreet($data['street']);
        $employee->setPostalCode($data['postalCode']);
        $employee->setCity($data['city']);
        $employee->setRoles(['ROLE_EMPLOYEE']);
        $employee->setIsActive(true);

        $entityManager->persist($employee);
        $entityManager->flush();

        return $this->json([
            'message' => 'Compte employé créé avec succès.',
            'employee' => [
                'id' => $employee->getId(),
                'email' => $employee->getEmail(),
                'firstName' => $employee->getFirstName(),
                'lastName' => $employee->getLastName(),
                'isActive' => $employee->isActive(),
                'roles' => $employee->getRoles(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/admin/employees/{id}/disable', name: 'app_admin_disable_employee', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function disableEmployee(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérifie que l'employé existe
        $employee = $userRepository->find($id);

        if (!$employee) {
            return $this->json([
                'message' => 'Employé introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifie que le compte est bien un employé
        if (!in_array('ROLE_EMPLOYEE', $employee->getRoles())) {
            return $this->json([
                'message' => 'Ce compte ne correspond pas à un employé.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie que le compte est encore actif
        if (!$employee->isActive()) {
            return $this->json([
                'message' => 'Ce compte est déjà désactivé.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Désactive le compte employé
        $employee->setIsActive(false);
        $employee->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Compte employé désactivé avec succès.',
            'employee' => [
                'id' => $employee->getId(),
                'isActive' => $employee->isActive(),
                'updatedAt' => $employee->getUpdatedAt()?->format('Y-m-d H:i:s')
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/api/admin/employees/{id}/restore', name: 'app_admin_restore_employee', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    public function restoreEmployee(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérifie que l'employé existe
        $employee = $userRepository->find($id);

        if (!$employee) {
            return $this->json([
                'message' => 'Employé introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifie que le compte est bien un employé
        if (!in_array('ROLE_EMPLOYEE', $employee->getRoles())) {
            return $this->json([
                'message' => 'Ce compte ne correspond pas à un employé.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie que le compte est désactivé
        if ($employee->isActive()) {
            return $this->json([
                'message' => 'Ce compte est déjà actif.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Réactive le compte employé
        $employee->setIsActive(true);
        $employee->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Compte employé réactivé avec succès.',
            'employee' => [
                'id' => $employee->getId(),
                'isActive' => $employee->isActive(),
                'updatedAt' => $employee->getUpdatedAt()?->format('Y-m-d H:i:s')
            ]
        ], Response::HTTP_OK);
    }
}
