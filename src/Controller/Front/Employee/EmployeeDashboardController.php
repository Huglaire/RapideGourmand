<?php

namespace App\Controller\Front\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe')]
class EmployeeDashboardController extends AbstractController
{
    #[Route('', name: 'employee_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('employee/dashboard/dashboard.html.twig');
    }
}