<?php

namespace App\Controller;

use App\Repository\ThemeRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ThemeController extends AbstractController
{
    #[Route('/api/themes', name: 'app_theme_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/themes',
        summary: 'Liste des thèmes',
        tags: ['Thèmes']
    )]
    public function index(
        ThemeRepository $themeRepository
    ): JsonResponse {

        $themes = $themeRepository->findBy(
            [],
            ['title' => 'ASC']
        );

        $data = [];

        foreach ($themes as $theme) {

            $data[] = [
                'id' => $theme->getId(),
                'title' => $theme->getTitle()
            ];

        }

        return $this->json($data);

    }
}