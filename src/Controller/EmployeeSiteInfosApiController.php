<?php

namespace App\Controller;

use App\Entity\SiteInfos;
use App\Repository\SiteInfosRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/site-infos')]
final class EmployeeSiteInfosApiController extends AbstractController
{
    /**
     * Retourne toutes les informations du site.
     */
    #[IsGranted('ROLE_EMPLOYEE')]
    #[Route('/employee', name: 'app_employee_site_infos_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/site-infos/employee',
        summary: 'Liste les informations du site',
        tags: ['Administration']
    )]
    public function index(
        SiteInfosRepository $siteInfosRepository
    ): JsonResponse {

        $siteInfos = $siteInfosRepository->findBy(
            [],
            ['identifier' => 'ASC']
        );

        $data = array_map(
            fn (SiteInfos $siteInfo) => [
                'id' => $siteInfo->getId(),
                'identifier' => $siteInfo->getIdentifier(),
                'value' => $siteInfo->getValue(),
            ],
            $siteInfos
        );

        return $this->json($data);

    }

    /**
     * Met à jour une information du site.
     */
    #[IsGranted('ROLE_EMPLOYEE')]
    #[Route('/{id}', name: 'app_employee_site_infos_update', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/site-infos/{id}',
        summary: 'Met à jour une information du site',
        tags: ['Administration']
    )]
    public function update(
        SiteInfos $siteInfo,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $payload = json_decode(
            $request->getContent(),
            true
        );

        if (
            !is_array($payload)
            || !array_key_exists('value', $payload)
        ) {

            return $this->json(
                [
                    'message' => 'Le champ "value" est obligatoire.'
                ],
                Response::HTTP_BAD_REQUEST
            );

        }

        $siteInfo->setValue(
            $payload['value']
        );

        $entityManager->flush();

        return $this->json(
            [
                'message' => 'Information mise à jour.'
            ]
        );

    }
}