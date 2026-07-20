<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\DietRepository;
use App\Repository\DishRepository;
use App\Repository\MenuRepository;
use App\Repository\PictureRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

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

            $themes = [];

            foreach ($menu->getTheme() as $theme) {

                $themes[] = [
                    'id' => $theme->getId(),
                    'title' => $theme->getTitle()
                ];
            }

            $diets = [];

            foreach ($menu->getDiets() as $diet) {

                $diets[] = [
                    'id' => $diet->getId(),
                    'title' => $diet->getTitle()
                ];
            }

            $pictures = [];

            foreach ($menu->getPictures() as $picture) {

                $pictures[] = [
                    'id' => $picture->getId(),
                    'title' => $picture->getTitle(),
                    'alt' => $picture->getAlt(),
                    'path' => $picture->getPath()
                ];
            }

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
                'themes' => $themes,
                'pictures' => $pictures,
                'diets' => $diets
            ];
        }

        return $this->json($data);
    }

    #[Route('/api/menus/{id}', name: 'app_menu_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/menus/{id}',
        summary: 'Consulter un menu par ID',
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
    public function show(
        int $id,
        MenuRepository $menuRepository
    ): JsonResponse {

        $menu = $menuRepository->find($id);

        if (!$menu || !$menu->isAvailable()) {

            return $this->json([
                'message' => 'Menu introuvable.'
            ], 404);
        }

        // Prépare les thèmes du menu.
        $themes = [];

        foreach ($menu->getTheme() as $theme) {

            $themes[] = [
                'id' => $theme->getId(),
                'title' => $theme->getTitle()
            ];
        }

        // Prépare les régimes alimentaires.
        $diets = [];

        foreach ($menu->getDiets() as $diet) {

            $diets[] = [
                'id' => $diet->getId(),
                'title' => $diet->getTitle()
            ];
        }

        // Prépare les images du menu.
        $pictures = [];

        foreach ($menu->getPictures() as $picture) {

            $pictures[] = [
                'id' => $picture->getId(),
                'title' => $picture->getTitle(),
                'alt' => $picture->getAlt(),
                'path' => $picture->getPath()
            ];
        }

        // Prépare les plats du menu.
        $dishes = [];

        foreach ($menu->getDishes() as $dish) {

            $dishPictures = [];

            foreach ($dish->getPictures() as $picture) {

                $dishPictures[] = [
                    'id' => $picture->getId(),
                    'title' => $picture->getTitle(),
                    'alt' => $picture->getAlt(),
                    'path' => $picture->getPath()
                ];
            }

            $allergens = [];

            foreach ($dish->getAllergens() as $allergen) {

                $allergens[] = [
                    'id' => $allergen->getId(),
                    'title' => $allergen->getTitle()
                ];
            }

            $dishes[] = [
                'id' => $dish->getId(),
                'title' => $dish->getTitle(),
                'description' => $dish->getDescription(),
                'price' => $dish->getPrice(),
                'pictures' => $dishPictures,
                'allergens' => $allergens
            ];
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
            'themes' => $themes,
            'diets' => $diets,
            'pictures' => $pictures,
            'dishes' => $dishes
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
                    new OA\Property(property: 'title', type: 'string', example: 'Buffet Prestige'),
                    new OA\Property(property: 'description', type: 'string', example: 'Le luxe dans votre assiette'),
                    new OA\Property(property: 'minimumGuestNumber', type: 'integer', example: 30),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 49.90),
                    new OA\Property(property: 'stock', type: 'integer', example: 6),
                    new OA\Property(property: 'conditions', type: 'string', example: 'Réservation 15 jours à l’avance'),
                    new OA\Property(property: 'themes', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                    new OA\Property(property: 'diets', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2]),
                    new OA\Property(property: 'pictures', type: 'array', items: new OA\Items(type: 'integer'), example: [1]),
                    new OA\Property(property: 'dishes', type: 'array', items: new OA\Items(type: 'integer'), example: [1, 2, 3])
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
        EntityManagerInterface $entityManager,
        ThemeRepository $themeRepository,
        DietRepository $dietRepository,
        PictureRepository $pictureRepository,
        DishRepository $dishRepository
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

        // Associe les thèmes et régimes sélectionnés au menu.
        foreach ($data['themes'] ?? [] as $themeId) {

            $theme = $themeRepository->find($themeId);

            if ($theme !== null) {
                $menu->addTheme($theme);
            }
        }

        foreach ($data['diets'] ?? [] as $dietId) {

            $diet = $dietRepository->find($dietId);

            if ($diet !== null) {
                $menu->addDiet($diet);
            }
        }

        // Associe les images sélectionnées au menu.
        foreach ($data['pictures'] ?? [] as $pictureId) {

            $picture = $pictureRepository->find($pictureId);

            if ($picture !== null) {
                $menu->addPicture($picture);
            }
        }

        // Associe les plats sélectionnés au menu.
        foreach ($data['dishes'] ?? [] as $dishId) {

            $dish = $dishRepository->find($dishId);

            if ($dish !== null) {
                $menu->addDish($dish);
            }
        }

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
            new OA\Parameter(name: 'id', in: 'path', description: 'Identifiant du menu', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string', example: 'Buffet Prestige'),
                    new OA\Property(property: 'description', type: 'string', example: 'Cocktail haut de gamme'),
                    new OA\Property(property: 'minimumGuestNumber', type: 'integer', example: 30),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 49.90),
                    new OA\Property(property: 'stock', type: 'integer', example: 12),
                    new OA\Property(property: 'conditions', type: 'string', example: 'Réservation 15 jours à l’avance'),
                    new OA\Property(property: 'themes', description: 'Liste des identifiants des thèmes.', type: 'array', items: new OA\Items(type: 'integer'), example: [6, 9]),
                    new OA\Property(property: 'diets', description: 'Liste des identifiants des régimes alimentaires.', type: 'array', items: new OA\Items(type: 'integer'), example: [4, 5])
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
        ThemeRepository $themeRepository,
        DietRepository $dietRepository,
        EntityManagerInterface $entityManager,
        PictureRepository $pictureRepository,
        DishRepository $dishRepository,
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

        // Met à jour les thèmes du menu.
        if (isset($data['themes'])) {

            foreach ($menu->getTheme() as $theme) {
                $menu->removeTheme($theme);
            }

            foreach ($data['themes'] as $themeId) {

                $theme = $themeRepository->find($themeId);

                if ($theme === null) {
                    return $this->json([
                        'message' => "Le thème d'identifiant $themeId est introuvable."
                    ], 400);
                }

                $menu->addTheme($theme);
            }
        }

        // Met à jour les régimes alimentaires du menu.
        if (isset($data['diets'])) {

            foreach ($menu->getDiets() as $diet) {
                $menu->removeDiet($diet);
            }

            foreach ($data['diets'] as $dietId) {

                $diet = $dietRepository->find($dietId);

                if ($diet === null) {
                    return $this->json([
                        'message' => "Le régime d'identifiant $dietId est introuvable."
                    ], 400);
                }

                $menu->addDiet($diet);
            }
        }

        // Met à jour les images du menu.
        if (isset($data['pictures'])) {

            foreach ($menu->getPictures() as $picture) {
                $menu->removePicture($picture);
            }

            foreach ($data['pictures'] as $pictureId) {

                $picture = $pictureRepository->find($pictureId);

                if ($picture === null) {
                    return $this->json([
                        'message' => "L'image d'identifiant $pictureId est introuvable."
                    ], 400);
                }

                $menu->addPicture($picture);
            }
        }

        // Met à jour les plats du menu.
        if (isset($data['dishes'])) {

            foreach ($menu->getDishes() as $dish) {
                $menu->removeDish($dish);
            }

            foreach ($data['dishes'] as $dishId) {

                $dish = $dishRepository->find($dishId);

                if ($dish === null) {
                    return $this->json([
                        'message' => "Le plat d'identifiant $dishId est introuvable."
                    ], 400);
                }

                $menu->addDish($dish);
            }
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
