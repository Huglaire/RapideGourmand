<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderHistoryController extends AbstractController
{
    #[Route('/mes-commandes', name: 'order_history', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('orderHistory/index.html.twig');
    }
}