<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

final class MenuController extends AbstractController
{
    #[Route('/api/menus', name: 'app_menu_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/menus',
        summary: 'Lister les menus disponibles',
        description: 'Retourne la liste des menus actuellement disponibles.',
        tags: ['Menus'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des menus récupérée avec succès.'
            )
        ]
    )]

    public function index(MenuRepository $menuRepository): JsonResponse
    {
        $menus = $menuRepository->findBy(
            ['isAvailable' => true],
            ['createdAt' => 'DESC']
        );

        $data = [];

        foreach ($menus as $menu) {
            $data[] = [
                'id' => $menu->getId(),
                'title' => $menu->getTitle(),
                'description' => $menu->getDescription(),
                'minimumGuestNumber' => $menu->getMinimumGuestNumber(),
                'price' => $menu->getPrice(),
                'stock' => $menu->getStock(),
                'conditions' => $menu->getConditions(),
                'isAvailable' => $menu->isAvailable(),
                'createdAt' => $menu->getCreatedAt()?->format(DATE_ATOM),
                'updatedAt' => $menu->getUpdatedAt()?->format(DATE_ATOM),
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/menus/{id}', name: 'app_menu_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/menus/{id}',
        summary: 'Consulter un menu par id',
        description: 'Retourne le détail d’un menu disponible.',
        tags: ['Menus'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Identifiant du menu',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Menu récupéré avec succès.'
            ),
            new OA\Response(
                response: 404,
                description: 'Menu introuvable.',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Menu introuvable.'
                        )
                    ]
                )
            )
        ]
    )]
    public function show(int $id, MenuRepository $menuRepository): JsonResponse
    {
        $menu = $menuRepository->find($id);

        if (!$menu || !$menu->isAvailable()) {
            return $this->json([
                'message' => 'Menu introuvable.'
            ], 404);
        }

        return $this->json([
            'id' => $menu->getId(),
            'title' => $menu->getTitle(),
            'description' => $menu->getDescription(),
            'minimumGuestNumber' => $menu->getMinimumGuestNumber(),
            'price' => $menu->getPrice(),
            'stock' => $menu->getStock(),
            'conditions' => $menu->getConditions(),
            'isAvailable' => $menu->isAvailable(),
            'createdAt' => $menu->getCreatedAt()?->format(DATE_ATOM),
            'updatedAt' => $menu->getUpdatedAt()?->format(DATE_ATOM),
        ]);
    }

    #[IsGranted('ROLE_EMPLOYEE', message: 'Action réservée aux employeurs ou admin.')]
    #[Route('/api/menus', name: 'app_menu_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/menus',
        summary: 'Créer un menu',
        description: 'Crée un nouveau menu.',
        tags: ['Menus'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'minimumGuestNumber', 'price', 'stock'],
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Menu coquin'),
                    new OA\Property(property: 'description', type: 'string', example: 'Menu avec des blagues'),
                    new OA\Property(property: 'minimumGuestNumber', type: 'integer', example: 12),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 14.70),
                    new OA\Property(property: 'stock', type: 'integer', example: 25),
                    new OA\Property(property: 'conditions', type: 'string', example: 'Aimer se taper sur les cuisses')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Menu créé avec succès.'),
            new OA\Response(response: 400, description: 'Les champs obligatoires sont manquants.'),
            new OA\Response(response: 403, description: 'Accès refusé.')
        ]
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (
            empty($data['title']) ||
            empty($data['minimumGuestNumber']) ||
            empty($data['price']) ||
            empty($data['stock'])
        ) {
            return $this->json([
                'message' => 'Les champs obligatoires sont manquants.'
            ], 400);
        }

        $menu = new Menu();

        $menu->setTitle($data['title']);
        $menu->setDescription($data['description'] ?? null);
        $menu->setMinimumGuestNumber($data['minimumGuestNumber']);
        $menu->setPrice($data['price']);
        $menu->setStock($data['stock']);
        $menu->setConditions($data['conditions'] ?? null);

        $entityManager->persist($menu);
        $entityManager->flush();

        return $this->json([
            'message' => 'Menu créé avec succès.',
            'id' => $menu->getId()
        ], 201);
    }

    #[IsGranted('ROLE_EMPLOYEE', message: 'Action réservée aux employeurs ou admin.')]
    #[Route('/api/menus/{id}', name: 'app_menu_update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/menus/{id}',
        summary: 'Modifier un menu par ID',
        description: 'Met à jour les informations d’un menu.',
        tags: ['Menus'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant du menu',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Menu Radin'),
                    new OA\Property(property: 'description', type: 'string', example: 'Menu pour les gens sans le sou'),
                    new OA\Property(property: 'minimumGuestNumber', type: 'integer', example: 10),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 2.30),
                    new OA\Property(property: 'stock', type: 'integer', example: 25),
                    new OA\Property(property: 'conditions', type: 'string', example: 'Réglable uniquement en cash')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Menu modifié avec succès.'),
            new OA\Response(response: 403, description: 'Accès refusé.'),
            new OA\Response(response: 404, description: 'Menu introuvable.')
        ]
    )]
    public function update(
        int $id,
        Request $request,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return $this->json([
                'message' => 'Menu introuvable.'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $menu->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $menu->setDescription($data['description']);
        }

        if (isset($data['minimumGuestNumber'])) {
            $menu->setMinimumGuestNumber($data['minimumGuestNumber']);
        }

        if (isset($data['price'])) {
            $menu->setPrice($data['price']);
        }

        if (isset($data['stock'])) {
            $menu->setStock($data['stock']);
        }

        if (isset($data['conditions'])) {
            $menu->setConditions($data['conditions']);
        }

        $menu->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Menu modifié avec succès.'
        ]);
    }

    #[IsGranted('ROLE_EMPLOYEE', message: 'Action réservée aux employeurs ou admin.')]
    #[Route('/api/menus/{id}', name: 'app_menu_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/menus/{id}',
        summary: 'Désactiver un menu par ID',
        description: 'Désactive un menu sans le supprimer définitivement.',
        tags: ['Menus'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant du menu',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Menu désactivé avec succès.'),
            new OA\Response(response: 403, description: 'Accès refusé.'),
            new OA\Response(response: 404, description: 'Menu introuvable.')
        ]
    )]
    public function delete(
        int $id,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return $this->json([
                'message' => 'Menu introuvable.'
            ], 404);
        }

        $menu->setIsAvailable(false);
        $menu->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Menu désactivé avec succès.'
        ]);
    }

    #[IsGranted('ROLE_EMPLOYEE', message: 'Action réservée aux employeurs ou admin.')]
    #[Route('/api/menus/{id}/restore', name: 'app_menu_restore', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/menus/{id}/restore',
        summary: 'Restaurer un menu par ID',
        description: 'Réactive un menu précédemment désactivé.',
        tags: ['Menus'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant du menu',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Menu restauré avec succès.'),
            new OA\Response(response: 400, description: 'Le menu est déjà disponible.'),
            new OA\Response(response: 403, description: 'Accès refusé.'),
            new OA\Response(response: 404, description: 'Menu introuvable.')
        ]
    )]
    public function restore(
        int $id,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $menu = $menuRepository->find($id);

        if (!$menu) {
            return $this->json([
                'message' => 'Menu introuvable.'
            ], 404);
        }

        if ($menu->isAvailable()) {
            return $this->json([
                'message' => 'Le menu est déjà disponible.'
            ], 400);
        }

        $menu->setIsAvailable(true);
        $menu->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Menu restauré avec succès.'
        ]);
    }
}
