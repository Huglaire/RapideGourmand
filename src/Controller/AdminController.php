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
use OpenApi\Attributes as OA;

final class AdminController extends AbstractController
{
    #[Route('/api/admin/employees', name: 'app_admin_create_employee', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Post(
        path: '/api/admin/employees',
        summary: 'Créer un compte employé',
        description: 'Crée un nouveau compte employé.',
        tags: ['Administration'],
        security: [['Bearer' => []]],
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
                    new OA\Property(property: 'email', type: 'string', example: 'employee@mail.fr'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                    new OA\Property(property: 'firstName', type: 'string', example: 'Miles'),
                    new OA\Property(property: 'lastName', type: 'string', example: 'Davis'),
                    new OA\Property(property: 'phone', type: 'string', example: '0612345678'),
                    new OA\Property(property: 'street', type: 'string', example: '12 rue de la trompette'),
                    new OA\Property(property: 'postalCode', type: 'string', example: '33000'),
                    new OA\Property(property: 'city', type: 'string', example: 'Bordeaux')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Compte employé créé avec succès.'),
            new OA\Response(response: 400, description: 'Requête invalide.'),
            new OA\Response(response: 403, description: 'Accès refusé.'),
            new OA\Response(response: 409, description: 'Adresse e-mail déjà utilisée.')
        ]
    )]
    public function createEmployee(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository
    ): JsonResponse {

        $data = json_decode(
            $request->getContent(),
            true
        );


        if (!is_array($data)) {

            return $this->json([
                'message' => 'Le corps de la requête est invalide.'
            ], Response::HTTP_BAD_REQUEST);

        }


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


        if ($userRepository->findOneBy(['email' => $data['email']])) {

            return $this->json([
                'message' => 'Cet email est déjà utilisé.'
            ], Response::HTTP_CONFLICT);

        }


        $employee = new User();

        $employee->setEmail($data['email']);
        $employee->setFirstName($data['firstName']);
        $employee->setLastName($data['lastName']);

        $employee->setPassword(
            $passwordHasher->hashPassword(
                $employee,
                $data['password']
            )
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
    #[OA\Patch(
        path: '/api/admin/employees/{id}/disable',
        summary: 'Désactiver un employé par ID',
        description: 'Désactive le compte d’un employé.',
        tags: ['Administration']
    )]
    public function disableEmployee(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $employee = $userRepository->find($id);


        if (!$employee) {

            return $this->json([
                'message' => 'Employé introuvable.'
            ], Response::HTTP_NOT_FOUND);

        }


        $employee->setIsActive(false);
        $employee->setUpdatedAt(
            new \DateTimeImmutable()
        );


        $entityManager->flush();


        return $this->json([
            'message' => 'Compte employé désactivé avec succès.'
        ]);
    }


    #[Route('/api/admin/employees/{id}/restore', name: 'app_admin_restore_employee', methods: ['PATCH'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Patch(
        path: '/api/admin/employees/{id}/restore',
        summary: 'Réactiver un employé par ID',
        description: 'Réactive le compte d’un employé.',
        tags: ['Administration']
    )]
    public function restoreEmployee(
        int $id,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $employee = $userRepository->find($id);


        if (!$employee) {

            return $this->json([
                'message' => 'Employé introuvable.'
            ], Response::HTTP_NOT_FOUND);

        }


        $employee->setIsActive(true);
        $employee->setUpdatedAt(
            new \DateTimeImmutable()
        );


        $entityManager->flush();


        return $this->json([
            'message' => 'Compte employé réactivé avec succès.'
        ]);
    }


    #[Route('/api/admin/employees', name: 'app_admin_get_employees', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/employees',
        summary: 'Lister les employés',
        tags: ['Administration']
    )]
    public function getEmployees(
        UserRepository $userRepository
    ): JsonResponse {

        $employees = $userRepository->findAll();

        $data = [];


        foreach ($employees as $employee) {

            if (!in_array('ROLE_EMPLOYEE', $employee->getRoles())) {
                continue;
            }


            $data[] = [
                'id' => $employee->getId(),
                'firstName' => $employee->getFirstName(),
                'lastName' => $employee->getLastName(),
                'email' => $employee->getEmail(),
                'phone' => $employee->getPhone(),
                'isActive' => $employee->isActive(),
            ];

        }


        return $this->json($data);
    }
}