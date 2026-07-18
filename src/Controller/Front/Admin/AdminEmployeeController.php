<?php

namespace App\Controller\Front\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/employees', name: 'front_admin_employees_')]
final class AdminEmployeeController extends AbstractController
{
    #[Route('', name: 'list')]
    public function index(): Response
    {
        return $this->render(
            'front/admin/employees/adminEmployees.html.twig'
        );
    }
}