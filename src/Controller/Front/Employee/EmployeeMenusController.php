<?php

namespace App\Controller\Front\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employe/menus')]
class EmployeeMenusController extends AbstractController
{
    #[Route('', name: 'employee_menus', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('employee/menu/menus.html.twig');
    }

    #[Route('/creer', name: 'employee_menu_create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('employee/menu/create.html.twig');
    }

    #[Route('/{id}/modifier', name: 'employee_menu_edit', methods: ['GET'])]
    public function edit(int $id): Response
    {
        return $this->render('employee/menu/edit.html.twig', [
            'menuId' => $id
        ]);
    }
}