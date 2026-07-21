<?php

namespace App\Controller;

use App\Entity\SiteInfos;
use App\Repository\SiteInfosRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

        // Récupère toutes les informations du site triées par identifiant.
        $siteInfos = $siteInfosRepository->findBy(
            [],
            ['identifier' => 'ASC']
        );

        // Prépare les données à renvoyer au format JSON.
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
}