<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

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
        summary: 'Ajouter une image',
        tags: ['Images']
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger
    ): JsonResponse {

        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        if (!$file) {

            return $this->json([
                'message' => 'Le fichier est obligatoire.'
            ], Response::HTTP_BAD_REQUEST);

        }

        $title = pathinfo(
            $file->getClientOriginalName(),
            PATHINFO_FILENAME
        );

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $filename = $slugger->slug($title);
        $filename .= '-' . uniqid() . '.' . $extension;


        try {

            $file->move(
                $this->getParameter('kernel.project_dir') . '/public/uploads',
                $filename
            );


            $picture = new Picture();

            $picture->setTitle($title);
            $picture->setAlt('');
            $picture->setPath('/uploads/' . $filename);


            $entityManager->persist($picture);
            $entityManager->flush();


        } catch (\Throwable $e) {

            return $this->json([
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);

        }


        return $this->json([
            'id' => $picture->getId(),
            'title' => $picture->getTitle(),
            'alt' => $picture->getAlt(),
            'path' => $picture->getPath()
        ], Response::HTTP_CREATED);
    }
}