<?php

namespace App\Controller;

use App\Repository\DietRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class DietController extends AbstractController
{
    #[Route('/api/diets', name: 'app_diet_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/diets',
        summary: 'Liste des régimes alimentaires',
        tags: ['Régimes']
    )]
    public function index(
        DietRepository $dietRepository
    ): JsonResponse {

        $diets = $dietRepository->findBy(
            [],
            ['title' => 'ASC']
        );

        $data = [];

        foreach ($diets as $diet) {

            $data[] = [
                'id' => $diet->getId(),
                'title' => $diet->getTitle()
            ];

        }

        return $this->json($data);

    }
}