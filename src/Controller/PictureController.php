<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class PictureController extends AbstractController
{
    #[Route('/api/pictures', name: 'app_picture_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/pictures',
        summary: 'Lister les images',
        tags: ['Images'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des images.'
            )
        ]
    )]
    public function index(
        PictureRepository $pictureRepository
    ): JsonResponse {

        $pictures = $pictureRepository->findBy(
            [],
            ['createdAt' => 'DESC']
        );

        $data = [];

        foreach ($pictures as $picture) {

            $data[] = [
                'id' => $picture->getId(),
                'title' => $picture->getTitle(),
                'alt' => $picture->getAlt(),
                'path' => $picture->getPath()
            ];
        }

        return $this->json($data);
    }

    #[IsGranted(
        'ROLE_EMPLOYEE',
        message: 'Action réservée aux employés ou administrateurs.'
    )]
    #[Route('/api/pictures', name: 'app_picture_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/pictures',
        summary: 'Créer une image',
        description: 'Ajoute une nouvelle image.',
        tags: ['Images'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['title', 'alt', 'path'],
                properties: [
                    new OA\Property(
                        property: 'title',
                        type: 'string',
                        example: 'Buffet Prestige'
                    ),
                    new OA\Property(
                        property: 'alt',
                        type: 'string',
                        example: 'Photo du buffet prestige'
                    ),
                    new OA\Property(
                        property: 'path',
                        type: 'string',
                        example: '/public/images/menus/AssiettesMains.jpg'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Image créée avec succès.'
            ),
            new OA\Response(
                response: 400,
                description: 'Les champs obligatoires sont manquants.'
            )
        ]
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        if (
            empty($data['title']) ||
            empty($data['alt']) ||
            empty($data['path'])
        ) {

            return $this->json([
                'message' => 'Les champs obligatoires sont manquants.'
            ], 400);
        }

        $picture = new Picture();

        $picture->setTitle($data['title']);
        $picture->setAlt($data['alt']);
        $picture->setPath($data['path']);

        $entityManager->persist($picture);
        $entityManager->flush();

        return $this->json([
            'message' => 'Image créée avec succès.',
            'id' => $picture->getId()
        ], 201);
    }
}
