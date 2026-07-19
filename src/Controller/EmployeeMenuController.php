<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/menus')]
final class EmployeeMenuController extends AbstractController
{
    #[IsGranted('ROLE_EMPLOYEE')]
    #[Route('/employee', name: 'app_employee_menu_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/menus/employee',
        summary: 'Liste tous les menus pour les employés',
        tags: ['Administration']
    )]
    public function index(
        MenuRepository $menuRepository
    ): JsonResponse {

        $menus = $menuRepository->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        $data = array_map(
            fn(Menu $menu) => [
                'id' => $menu->getId(),
                'title' => $menu->getTitle(),
                'price' => $menu->getPrice(),
                'stock' => $menu->getStock(),
                'isAvailable' => $menu->isAvailable()
            ],
            $menus
        );

        return $this->json($data);
    }
}
