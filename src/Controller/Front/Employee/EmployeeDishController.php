<?php

namespace App\Controller\Front\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employe/plats')]
class EmployeeDishController extends AbstractController
{
    #[Route('', name: 'employee_dishes', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('employee/dish/dishes.html.twig');
    }

    #[Route('/creer', name: 'employee_dish_create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('employee/dish/dish.create.html.twig');
    }

    #[Route('/{id}/modifier', name: 'employee_dish_edit', methods: ['GET'])]
    public function edit(int $id): Response
    {
        return $this->render('employee/dish/dish.edit.html.twig', [
            'dishId' => $id,
        ]);
    }
}