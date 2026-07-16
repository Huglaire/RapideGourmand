<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Allergen;
use App\Repository\AllergenRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class AllergenController extends AbstractController
{
    #[Route('/api/allergens', name: 'app_allergen_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/allergens',
        summary: 'Lister les allergènes',
        tags: ['Allergènes'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des allergènes.'
            )
        ]
    )]
    public function index(
        AllergenRepository $allergenRepository
    ): JsonResponse {

        $allergens = $allergenRepository->findBy(
            [],
            ['title' => 'ASC']
        );

        $data = [];

        foreach ($allergens as $allergen) {

            $data[] = [
                'id' => $allergen->getId(),
                'title' => $allergen->getTitle()
            ];
        }

        return $this->json($data);
    }

    #[IsGranted('ROLE_EMPLOYEE', message: 'Action réservée aux employés ou administrateurs.')]
    #[Route('/api/allergens', name: 'app_allergen_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/allergens',
        summary: 'Créer un allergène',
        description: 'Crée un nouvel allergène.',
        tags: ['Allergènes'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title'],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        example: 'Gluten'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Allergène créé avec succès.'
            ),
            new OA\Response(
                response: 400,
                description: 'Le titre est obligatoire.'
            ),
            new OA\Response(
                response: 409,
                description: 'Cet allergène existe déjà.'
            )
        ]
    )]
    public function create(
        Request $request,
        AllergenRepository $allergenRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {

            return $this->json([
                'message' => 'Le titre est obligatoire.'
            ], 400);
        }

        $existingAllergen = $allergenRepository->findOneBy([
            'title' => $data['title']
        ]);

        if ($existingAllergen !== null) {

            return $this->json([
                'message' => 'Cet allergène existe déjà.'
            ], 409);
        }

        $allergen = new Allergen();

        $allergen->setTitle($data['title']);

        $entityManager->persist($allergen);
        $entityManager->flush();

        return $this->json([
            'message' => 'Allergène créé avec succès.',
            'id' => $allergen->getId()
        ], 201);
    }
}
