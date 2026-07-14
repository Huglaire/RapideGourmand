<?php

namespace App\Controller\Front;

use App\Service\Front\MenuService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MenuController extends AbstractController
{
    // Affiche la liste des menus
    #[Route('/menus', name: 'menu_index')]
    public function index(MenuService $menuService): Response
    {
        $menus = $menuService->getAvailableMenus();

        return $this->render('menu/index.html.twig', [
            'menus' => $menus,
        ]);
    }
}