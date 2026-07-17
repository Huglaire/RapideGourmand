<?php

namespace App\Controller\Front\Employee;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/employe/avis')]
#[IsGranted('ROLE_EMPLOYEE')]
class EmployeeReviewsController extends AbstractController
{
    #[Route('', name: 'employee_reviews', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('employee/review/index.html.twig');
    }
}