<?php

namespace App\Controller\Front\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'front_admin_')]
final class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'dashboard')]
    public function index(): Response
    {
        return $this->render(
            'front/admin/dashboard/adminDashboard.html.twig'
        );
    }
}
