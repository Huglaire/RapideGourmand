<?php

namespace App\Controller\Front\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe')]
#[IsGranted('ROLE_EMPLOYEE')]
class EmployeeDashboardController extends AbstractController
{
    #[Route('', name: 'employee_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('employee/dashboard/index.html.twig');
    }
}