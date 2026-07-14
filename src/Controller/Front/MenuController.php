<?php

namespace App\Controller\Front;

use App\Service\Front\MenuService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MenuController extends AbstractController
{
    #[Route('/menus', name: 'menu_index')]
    public function index(
        Request $request,
        MenuService $menuService
    ): Response {
        $filters = [
            'themes' => $request->query->all('themes'),
            'diets' => $request->query->all('diets'),
            'guestNumber' => $request->query->get('guestNumber'),
            'price' => $request->query->get('price'),
        ];

        return $this->render(
            'menu/index.html.twig',
            $menuService->getMenuPageData($filters)
        );
    }
}