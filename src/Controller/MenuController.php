<?php

namespace App\Controller;

use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

final class MenuController extends AbstractController
{
    #[Route('/api/menus', name: 'app_menu_index', methods: ['GET'])]
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

    #[Route('/api/menus', name: 'app_menu_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
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

    #[Route('/api/menus/{id}', name: 'app_menu_update', methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
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

    #[Route('/api/menus/{id}', name: 'app_menu_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
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

    #[Route('/api/menus/{id}/restore', name: 'app_menu_restore', methods: ['PATCH'])]
    public function restore(
        int $id,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
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