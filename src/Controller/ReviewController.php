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
use OpenApi\Attributes as OA;

final class ReviewController extends AbstractController
{
    #[Route('/api/reviews', name: 'app_review_create', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        path: '/api/reviews',
        summary: 'Créer un avis',
        description: 'Crée un nouvel avis pour l’utilisateur connecté.',
        tags: ['Avis'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['rating'],
                properties: [
                    new OA\Property(property: 'rating', type: 'integer', example: 5),
                    new OA\Property(property: 'comment', type: 'string', example: 'Très satisfait de ce repas.')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Avis créé avec succès.'),
            new OA\Response(response: 400, description: 'Requête invalide.'),
            new OA\Response(response: 401, description: 'Utilisateur non connecté.'),
            new OA\Response(response: 409, description: 'Un avis existe déjà pour cet utilisateur.')
        ]
    )]
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
    #[OA\Get(
        path: '/api/reviews',
        summary: 'Lister les avis',
        description: 'Retourne la liste des avis validés.',
        tags: ['Avis'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des avis récupérée avec succès.'
            )
        ]
    )]
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
    #[OA\Patch(
        path: '/api/reviews/{id}',
        summary: 'Modifier un avis par ID',
        description: 'Met à jour un avis appartenant à l’utilisateur connecté.',
        tags: ['Avis'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant de l’avis',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'rating', type: 'integer', example: 4),
                    new OA\Property(property: 'comment', type: 'string', example: 'Très bonne prestation.')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Avis modifié avec succès.'),
            new OA\Response(response: 400, description: 'Requête invalide.'),
            new OA\Response(response: 403, description: 'Vous ne pouvez modifier que votre propre avis.'),
            new OA\Response(response: 404, description: 'Avis introuvable.')
        ]
    )]
    public function update(
        int $id,
        Request $request,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
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
    #[OA\Delete(
        path: '/api/reviews/{id}',
        summary: 'Supprimer un avis par ID',
        description: 'Supprime un avis appartenant à l’utilisateur connecté.',
        tags: ['Avis'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant de l’avis',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Avis supprimé avec succès.'),
            new OA\Response(response: 403, description: 'Vous ne pouvez supprimer que votre propre avis.'),
            new OA\Response(response: 404, description: 'Avis introuvable.')
        ]
    )]
    public function delete(
        int $id,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
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

    #[Route('/api/admin/reviews', name: 'app_admin_review_list', methods: ['GET'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    #[OA\Get(
        path: '/api/admin/reviews',
        summary: 'Lister les avis en attente',
        description: 'Retourne la liste des avis en attente de modération.',
        tags: ['Administration'],
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Liste des avis récupérée avec succès.'),
            new OA\Response(response: 403, description: 'Accès refusé.')
        ]
    )]
    public function adminIndex(ReviewRepository $reviewRepository): JsonResponse
    {
        // Retourne uniquement les avis en attente de modération
        $reviews = $reviewRepository->findBy(
            ['status' => 'En attente'],
            ['createdAt' => 'DESC']
        );

        $data = [];

        foreach ($reviews as $review) {
            $data[] = [
                'id' => $review->getId(),
                'rating' => $review->getRating(),
                'comment' => $review->getComment(),
                'status' => $review->getStatus(),
                'createdAt' => $review->getCreatedAt()?->format('Y-m-d H:i:s'),
                'user' => [
                    'id' => $review->getUser()->getId(),
                    'firstName' => $review->getUser()->getFirstName(),
                    'lastName' => $review->getUser()->getLastName(),
                ],
            ];
        }

        return $this->json($data, Response::HTTP_OK);
    }

    #[Route('/api/admin/reviews/{id}/approve', name: 'app_admin_review_approve', methods: ['PATCH'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    #[OA\Patch(
        path: '/api/admin/reviews/{id}/approve',
        summary: 'Valider un avis par ID',
        description: 'Valide un avis en attente de modération.',
        tags: ['Administration'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant de l’avis',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Avis validé avec succès.'),
            new OA\Response(response: 400, description: 'Seuls les avis en attente peuvent être validés.'),
            new OA\Response(response: 404, description: 'Avis introuvable.'),
            new OA\Response(response: 403, description: 'Accès refusé.')
        ]
    )]
    public function approve(
        int $id,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérifie que l'avis existe
        $review = $reviewRepository->find($id);

        if (!$review) {
            return $this->json([
                'message' => 'Avis introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifie que l'avis est en attente de modération
        if ($review->getStatus() !== 'En attente') {
            return $this->json([
                'message' => 'Seuls les avis en attente peuvent être validés.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Valide l'avis
        $review->setStatus('Validé');
        $review->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Avis validé avec succès.',
            'review' => [
                'id' => $review->getId(),
                'status' => $review->getStatus(),
                'updatedAt' => $review->getUpdatedAt()?->format('Y-m-d H:i:s')
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/api/admin/reviews/{id}/reject', name: 'app_admin_review_reject', methods: ['PATCH'])]
    #[IsGranted('ROLE_EMPLOYEE')]
    #[OA\Patch(
        path: '/api/admin/reviews/{id}/reject',
        summary: 'Refuser un avis par ID',
        description: 'Refuse un avis en attente de modération.',
        tags: ['Administration'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant de l’avis',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Avis refusé avec succès.'),
            new OA\Response(response: 400, description: 'Seuls les avis en attente peuvent être refusés.'),
            new OA\Response(response: 404, description: 'Avis introuvable.'),
            new OA\Response(response: 403, description: 'Accès refusé.')
        ]
    )]
    public function reject(
        int $id,
        ReviewRepository $reviewRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérifie que l'avis existe
        $review = $reviewRepository->find($id);

        if (!$review) {
            return $this->json([
                'message' => 'Avis introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        // Vérifie que l'avis est en attente de modération
        if ($review->getStatus() !== 'En attente') {
            return $this->json([
                'message' => 'Seuls les avis en attente peuvent être refusés.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Refuse l'avis
        $review->setStatus('Refusé');
        $review->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'Avis refusé avec succès.',
            'review' => [
                'id' => $review->getId(),
                'status' => $review->getStatus(),
                'updatedAt' => $review->getUpdatedAt()?->format('Y-m-d H:i:s')
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/api/reviews/me', name: 'app_review_me', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        path: '/api/reviews/me',
        summary: 'Récupérer mon avis',
        description: 'Retourne l’avis de l’utilisateur connecté.',
        tags: ['Avis'],
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Avis trouvé.'),
            new OA\Response(response: 404, description: 'Aucun avis trouvé.')
        ]
    )]
    public function me(ReviewRepository $reviewRepository): JsonResponse
    {
        $review = $reviewRepository->findOneBy([
            'user' => $this->getUser()
        ]);

        if (!$review) {
            return $this->json([
                'message' => 'Aucun avis.'
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'id' => $review->getId(),
            'rating' => $review->getRating(),
            'comment' => $review->getComment(),
            'status' => $review->getStatus(),
            'createdAt' => $review->getCreatedAt()?->format('Y-m-d H:i:s'),
        ]);
    }
}
