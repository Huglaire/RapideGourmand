<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class OrderController extends AbstractController
{
    #[Route('/commande', name: 'app_order', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('order/index.html.twig');
    }
}