<?php

namespace App\Controller;

use App\Entity\Dish;
use App\Repository\AllergenRepository;
use App\Repository\DishRepository;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DishController extends AbstractController
{
    #[Route('/api/dishes', name: 'app_dish_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/dishes',
        summary: 'Lister les plats',
        description: 'Retourne la liste des plats disponibles.',
        tags: ['Plats'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des plats récupérée avec succès.'
            )
        ]
    )]
    public function index(
        DishRepository $dishRepository
    ): JsonResponse {

        $dishes = $dishRepository->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        $data = [];

        foreach ($dishes as $dish) {

            // Prépare les images du plat.
            $pictures = [];

            foreach ($dish->getPictures() as $picture) {

                $pictures[] = [
                    'id' => $picture->getId(),
                    'title' => $picture->getTitle(),
                    'alt' => $picture->getAlt(),
                    'path' => $picture->getPath()
                ];
            }

            // Prépare les allergènes du plat.
            $allergens = [];

            foreach ($dish->getAllergens() as $allergen) {

                $allergens[] = [
                    'id' => $allergen->getId(),
                    'title' => $allergen->getTitle()
                ];
            }

            $data[] = [
                'id' => $dish->getId(),
                'title' => $dish->getTitle(),
                'description' => $dish->getDescription(),
                'price' => $dish->getPrice(),
                'createdAt' => $dish->getCreatedAt()?->format(DATE_ATOM),
                'updatedAt' => $dish->getUpdatedAt()?->format(DATE_ATOM),
                'pictures' => $pictures,
                'allergens' => $allergens
            ];
        }

        return $this->json($data);
    }

    #[IsGranted(
        'ROLE_EMPLOYEE',
        message: 'Action réservée aux employés ou administrateurs.'
    )]
    #[Route('/api/dishes', name: 'app_dish_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/dishes',
        summary: 'Créer un plat',
        description: 'Crée un nouveau plat.',
        tags: ['Plats'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'price'],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        example: 'Filet de bœuf'
                    ),
                    new OA\Property(
                        property: 'description',
                        type: 'string',
                        example: 'Sauce aux morilles'
                    ),
                    new OA\Property(
                        property: 'price',
                        type: 'number',
                        format: 'float',
                        example: 18.90
                    ),
                    new OA\Property(
                        property: 'pictures',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [1, 2]
                    ),
                    new OA\Property(
                        property: 'allergens',
                        type: 'array',
                        items: new OA\Items(type: 'integer'),
                        example: [3, 4]
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Plat créé avec succès.'
            ),
            new OA\Response(
                response: 400,
                description: 'Les champs obligatoires sont manquants.'
            ),
            new OA\Response(
                response: 403,
                description: 'Accès refusé.'
            )
        ]
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        PictureRepository $pictureRepository,
        AllergenRepository $allergenRepository
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (
            empty($data['title']) ||
            empty($data['price'])
        ) {

            return $this->json([
                'message' => 'Les champs obligatoires sont manquants.'
            ], 400);
        }

        $dish = new Dish();

        $dish->setTitle($data['title']);
        $dish->setDescription($data['description'] ?? null);
        $dish->setPrice($data['price']);

        // Associe les images au plat.
        foreach ($data['pictures'] ?? [] as $pictureId) {

            $picture = $pictureRepository->find($pictureId);

            if ($picture !== null) {
                $dish->addPicture($picture);
            }
        }

        // Associe les allergènes au plat.
        foreach ($data['allergens'] ?? [] as $allergenId) {

            $allergen = $allergenRepository->find($allergenId);

            if ($allergen !== null) {
                $dish->addAllergen($allergen);
            }
        }

        $entityManager->persist($dish);
        $entityManager->flush();

        return $this->json([
            'message' => 'Plat créé avec succès.',
            'id' => $dish->getId()
        ], 201);
    }

    #[Route('/api/dishes/{id}', name: 'app_dish_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/dishes/{id}',
        summary: 'Consulter un plat',
        description: 'Retourne le détail d’un plat.',
        tags: ['Plats'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant du plat',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plat récupéré avec succès.'
            ),
            new OA\Response(
                response: 404,
                description: 'Plat introuvable.'
            )
        ]
    )]
    public function show(
        int $id,
        DishRepository $dishRepository
    ): JsonResponse {

        $dish = $dishRepository->find($id);

        if (!$dish) {

            return $this->json([
                'message' => 'Plat introuvable.'
            ], 404);
        }

        // Prépare les images du plat.
        $pictures = [];

        foreach ($dish->getPictures() as $picture) {

            $pictures[] = [
                'id' => $picture->getId(),
                'title' => $picture->getTitle(),
                'alt' => $picture->getAlt(),
                'path' => $picture->getPath()
            ];
        }

        // Prépare les allergènes du plat.
        $allergens = [];

        foreach ($dish->getAllergens() as $allergen) {

            $allergens[] = [
                'id' => $allergen->getId(),
                'title' => $allergen->getTitle()
            ];
        }

        return $this->json([
            'id' => $dish->getId(),
            'title' => $dish->getTitle(),
            'description' => $dish->getDescription(),
            'price' => $dish->getPrice(),
            'createdAt' => $dish->getCreatedAt()?->format(DATE_ATOM),
            'updatedAt' => $dish->getUpdatedAt()?->format(DATE_ATOM),
            'pictures' => $pictures,
            'allergens' => $allergens
        ]);
    }

    #[IsGranted(
        'ROLE_EMPLOYEE',
        message: 'Action réservée aux employés ou administrateurs.'
    )]
    #[Route('/api/dishes/{id}', name: 'app_dish_update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/dishes/{id}',
        summary: 'Modifier un plat',
        description: 'Met à jour les informations d’un plat.',
        tags: ['Plats'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant du plat',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plat modifié avec succès.'
            ),
            new OA\Response(
                response: 404,
                description: 'Plat introuvable.'
            )
        ]
    )]
    public function update(
        int $id,
        Request $request,
        DishRepository $dishRepository,
        PictureRepository $pictureRepository,
        AllergenRepository $allergenRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $dish = $dishRepository->find($id);

        if (!$dish) {

            return $this->json([
                'message' => 'Plat introuvable.'
            ], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $dish->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $dish->setDescription($data['description']);
        }

        if (isset($data['price'])) {
            $dish->setPrice($data['price']);
        }

        // Met à jour les images du plat.
        if (isset($data['pictures'])) {

            foreach ($dish->getPictures() as $picture) {
                $dish->removePicture($picture);
            }

            foreach ($data['pictures'] as $pictureId) {

                $picture = $pictureRepository->find($pictureId);

                if ($picture !== null) {
                    $dish->addPicture($picture);
                }
            }
        }

        // Met à jour les allergènes du plat.
        if (isset($data['allergens'])) {

            foreach ($dish->getAllergens() as $allergen) {
                $dish->removeAllergen($allergen);
            }

            foreach ($data['allergens'] as $allergenId) {

                $allergen = $allergenRepository->find($allergenId);

                if ($allergen !== null) {
                    $dish->addAllergen($allergen);
                }
            }
        }

        $dish->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Plat modifié avec succès.'
        ]);
    }

    #[IsGranted(
        'ROLE_EMPLOYEE',
        message: 'Action réservée aux employés ou administrateurs.'
    )]
    #[Route('/api/dishes/{id}', name: 'app_dish_delete', methods: ['DELETE'])]
    #[OA\Delete(
        path: '/api/dishes/{id}',
        summary: 'Supprimer un plat',
        description: 'Supprime logiquement un plat.',
        tags: ['Plats'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                description: 'Identifiant du plat',
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Plat supprimé avec succès.'
            ),
            new OA\Response(
                response: 404,
                description: 'Plat introuvable.'
            )
        ]
    )]
    public function delete(
        int $id,
        DishRepository $dishRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $dish = $dishRepository->find($id);

        if (!$dish) {

            return $this->json([
                'message' => 'Plat introuvable.'
            ], 404);
        }

        $entityManager->remove($dish);
        $entityManager->flush();

        return $this->json([
            'message' => 'Plat supprimé avec succès.'
        ]);
    }
}
