<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MenuDetailController extends AbstractController
{
    #[Route('/menus/{id}', name: 'menu_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        return $this->render('menu/show.html.twig', [
            'menuId' => $id
        ]);
    }
}