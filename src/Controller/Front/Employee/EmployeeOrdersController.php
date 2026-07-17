<?php

namespace App\Controller\Front\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe/commandes')]
#[IsGranted('ROLE_EMPLOYEE')]
class EmployeeOrdersController extends AbstractController
{
    #[Route('', name: 'employee_orders', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('employee/order/index.html.twig');
    }
}