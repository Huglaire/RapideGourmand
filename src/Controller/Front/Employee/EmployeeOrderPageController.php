<?php

namespace App\Controller\Front;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_EMPLOYEE')]
final class EmployeeOrderPageController extends AbstractController
{
    #[Route(
        '/employe/commandes/{id}',
        name: 'employee_orders_show_page',
        methods: ['GET']
    )]
    public function show(
        int $id
    ): Response {

        return $this->render(
            'employee/order/show.html.twig',
            [
                'orderId' => $id
            ]
        );
    }
}