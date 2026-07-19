<?php

namespace App\Controller\Front\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminStatisticsController extends AbstractController
{
    #[Route('/admin/statistics', name: 'front_admin_statistics')]
    public function index(): Response
    {
        return $this->render(
            'front/admin/statistics/adminStatistics.html.twig'
        );
    }
}