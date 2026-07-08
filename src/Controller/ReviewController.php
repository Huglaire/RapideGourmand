<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ReviewController extends AbstractController
{
    #[Route('/api/reviews', name: 'app_review_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ReviewRepository $reviewRepository
    ): JsonResponse {
        $user = $this->getUser();

        // Vérifie si l'utilisateur a déjà publié un avis
        if ($reviewRepository->findOneBy(['user' => $user])) {
            return $this->json([
                'message' => 'Vous avez déjà publié un avis.'
            ], Response::HTTP_CONFLICT);
        }

        $data = json_decode($request->getContent(), true);

        // Vérifie que le corps de la requête contient un JSON valide
        if (!is_array($data)) {
            return $this->json([
                'message' => 'Le corps de la requête est invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie que la note est renseignée
        if (!isset($data['rating'])) {
            return $this->json([
                'message' => 'La note est obligatoire.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérifie que la note est bien comprise entre 1 et 5
        if (!is_int($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
            return $this->json([
                'message' => 'La note doit être un entier compris entre 1 et 5.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $review = new Review();
        $review->setRating($data['rating']);
        $review->setComment($data['comment'] ?? null);
        $review->setUser($user);

        $entityManager->persist($review);
        $entityManager->flush();

        return $this->json([
            'message' => 'Avis créé avec succès.',
            'review' => [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'status' => $review->getStatus(),
                'createdAt' => $review->getCreatedAt()?->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/reviews', name: 'app_review_list', methods: ['GET'])]
    public function index(ReviewRepository $reviewRepository): JsonResponse
    {
        // Retourner uniquement les avis validés et par ordre du plus récent au plus ancien
        $reviews = $reviewRepository->findBy(
            ['status' => 'Validé'],
            ['createdAt' => 'DESC']
        );

        $data = [];

        foreach ($reviews as $review) {
            $data[] = [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'createdAt' => $review->getCreatedAt()?->format('Y-m-d H:i:s'),
                'user' => [
                'firstName' => $review->getUser()->getFirstName(),
                ],
            ];
        }

        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/api/reviews/{id}', name: 'app_review_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    public function update(
        int $id,
        Request $request,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $user = $this->getUser();

        // Vérifie que l'avis existe
        $review = $reviewRepository->find($id);

        if (!$review) {
            return $this->json([
                'message' => 'Avis introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifie que l'utilisateur est le propriétaire de l'avis
        if ($review->getUser() !== $user) {
            return $this->json([
                'message' => 'Vous ne pouvez modifier que votre propre avis.'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        // Vérifie que le corps de la requête contient un JSON valide
        if (!is_array($data)) {
            return $this->json([
                'message' => 'Le corps de la requête est invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Met à jour la note si elle est renseignée
        if (isset($data['rating'])) {

            // Vérifie que la note est un entier compris entre 1 et 5
            if (!is_int($data['rating']) || $data['rating'] < 1 || $data['rating'] > 5) {
                return $this->json([
                    'message' => 'La note doit être un entier compris entre 1 et 5.'
                ], Response::HTTP_BAD_REQUEST);
            }

            $review->setRating($data['rating']);
        }

        // Met à jour le commentaire si celui-ci est renseigné
        if (array_key_exists('comment', $data)) {
            $review->setComment($data['comment']);
        }

        // Remet l'avis en attente de validation
        $review->setStatus('En attente');

        // Met à jour la date de modification
        $review->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Avis modifié avec succès.',
            'review' => [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'status' => $review->getStatus(),
                'updatedAt' => $review->getUpdatedAt()?->format('Y-m-d H:i:s')
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/api/reviews/{id}', name: 'app_review_delete', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        int $id,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $user = $this->getUser();

        // Vérifie que l'avis existe
        $review = $reviewRepository->find($id);

        if (!$review) {
            return $this->json([
                'message' => 'Avis introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifie que l'utilisateur est le propriétaire de l'avis
        if ($review->getUser() !== $user) {
            return $this->json([
                'message' => 'Vous ne pouvez supprimer que votre propre avis.'
            ], Response::HTTP_FORBIDDEN);
        }

        $entityManager->remove($review);
        $entityManager->flush();

        return $this->json([
            'message' => 'Avis supprimé avec succès.'
        ], Response::HTTP_OK);
    }
}
