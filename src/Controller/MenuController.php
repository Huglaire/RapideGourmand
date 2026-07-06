<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class MenuController extends AbstractController
{
    #[Route('/api/menus', name: 'app_menu_index', methods: ['GET'])]
    public function index(MenuRepository $menuRepository): JsonResponse
    {
        $menus = $menuRepository->findAll();

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
    public function show(int $id, MenuRepository $menuRepository): JsonResponse
    {
        $menu = $menuRepository->find($id);

        if (!$menu) {
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
}