<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AccountController extends AbstractController
{
    #[Route(
        '/account/reactivate',
        name: 'account_reactivate',
        methods: ['GET']
    )]
    public function reactivate(): Response
    {
        return $this->render(
            'account/reactivate.html.twig'
        );
    }
}