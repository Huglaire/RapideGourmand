<?php

namespace App\Controller\Front\Employee;

use App\Repository\ReviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/employe/avis')]
class EmployeeReviewsController extends AbstractController
{
    #[Route('', name: 'employee_reviews', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('employee/review/index.html.twig');
    }

    #[Route('/en-attente', name: 'employee_reviews_pending', methods: ['GET'])]
    public function pending(ReviewRepository $reviewRepository): Response
    {
        return $this->renderList(
            $reviewRepository,
            'En attente',
            'Avis à modérer'
        );
    }

    #[Route('/valides', name: 'employee_reviews_approved', methods: ['GET'])]
    public function approved(ReviewRepository $reviewRepository): Response
    {
        return $this->renderList(
            $reviewRepository,
            'Validé',
            'Avis validés'
        );
    }

    #[Route('/refuses', name: 'employee_reviews_rejected', methods: ['GET'])]
    public function rejected(ReviewRepository $reviewRepository): Response
    {
        return $this->renderList(
            $reviewRepository,
            'Refusé',
            'Avis refusés'
        );
    }

    /**
     * Affiche la liste des avis correspondant à un statut.
     */
    private function renderList(
        ReviewRepository $reviewRepository,
        string $status,
        string $title
    ): Response {
        return $this->render('employee/review/list.html.twig', [
            'title' => $title,
            'reviews' => $reviewRepository->findByStatus($status),
        ]);
    }
}
