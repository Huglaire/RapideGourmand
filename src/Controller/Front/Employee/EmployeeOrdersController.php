<?php

namespace App\Controller\Front\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe/commandes')]
class EmployeeOrdersController extends AbstractController
{
    #[Route('', name: 'employee_orders', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('employee/order/index.html.twig');
    }

    #[Route('/liste', name: 'employee_orders_list', methods: ['GET'])]
    public function list(): Response
    {
        return $this->render('employee/order/list.html.twig');
    }

    #[Route('/{id}', name: 'employee_orders_show', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render(
            'employee/order/show.html.twig'
        );
    }
}
