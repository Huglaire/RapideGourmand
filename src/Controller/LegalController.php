<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LegalController extends AbstractController
{
    #[Route(
        '/conditions-generales-de-vente',
        name: 'terms_and_conditions',
        methods: ['GET']
    )]
    public function termsAndConditions(): Response
    {
        return $this->render(
            'front/legal/terms_and_conditions.html.twig'
        );
    }
}