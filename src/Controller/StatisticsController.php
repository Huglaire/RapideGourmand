<?php

namespace App\Controller;

use App\Service\StatisticsQueryService;
use App\Repository\MenuRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Attributes as OA;

final class StatisticsController extends AbstractController
{
    #[Route('/api/admin/statistics/orders-per-menu', name: 'admin_statistics_orders', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/statistics/orders-per-menu',
        summary: 'Afficher le nombre de commandes par menu',
        description: 'Retourne le nombre de commandes par menu à partir des statistiques stockées dans MongoDB.',
        tags: ['Statistiques'],
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques récupérées avec succès.'
            ),
            new OA\Response(
                response: 403,
                description: 'Accès refusé.'
            )
        ]
    )]
    public function ordersPerMenu(
        StatisticsQueryService $statisticsQueryService,
        MenuRepository $menuRepository,
    ): JsonResponse {

        // Récupère les statistiques depuis MongoDB
        $statistics = $statisticsQueryService->getOrdersPerMenu();

        return $this->json(
            $this->buildMenuStatisticsResponse(
                $statistics,
                $menuRepository,
                'orderCount',
            )
        );
    }

    #[Route('/api/admin/statistics/revenue', name: 'admin_statistics_revenue', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    #[OA\Get(
        path: '/api/admin/statistics/revenue',
        summary: 'Afficher le chiffre d’affaires par menu',
        description: 'Retourne le chiffre d’affaires par menu avec possibilité de filtrer par menu et par période.',
        tags: ['Statistiques'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'menuId',
                in: 'query',
                description: 'Identifiant du menu',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'start',
                in: 'query',
                description: 'Date de début (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            ),
            new OA\Parameter(
                name: 'end',
                in: 'query',
                description: 'Date de fin (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Statistiques récupérées avec succès.'
            ),
            new OA\Response(
                response: 400,
                description: 'Format de date invalide.'
            ),
            new OA\Response(
                response: 403,
                description: 'Accès refusé.'
            )
        ]
    )]
    public function revenue(
        Request $request,
        StatisticsQueryService $statisticsQueryService,
        MenuRepository $menuRepository,
    ): JsonResponse {

        // Récupère les filtres envoyés dans l'URL
        $menuId = $request->query->getInt('menuId') ?: null;

        try {
            // Convertit les paramètres de dates en objets DateTime
            $start = $request->query->get('start')
                ? new \DateTime($request->query->get('start'))
                : null;

            $end = $request->query->get('end')
                ? new \DateTime($request->query->get('end'))
                : null;
        } catch (\Exception) {
            return $this->json(
                [
                    'message' => 'Les paramètres "start" et "end" doivent être des dates valides.',
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        // Récupère les statistiques depuis MongoDB
        $statistics = $statisticsQueryService->getRevenueByMenu(
            $menuId,
            $start,
            $end,
        );

        return $this->json(
            $this->buildMenuStatisticsResponse(
                $statistics,
                $menuRepository,
                'revenue',
            )
        );
    }

    /**
     * Construit la réponse JSON en enrichissant les statistiques MongoDB
     * avec les informations des menus stockées dans MySQL.
     */
    private function buildMenuStatisticsResponse(
        array $statistics,
        MenuRepository $menuRepository,
        string $valueField,
    ): array {

        // Récupère les identifiants des menus retournés par MongoDB
        $menuIds = array_unique(array_column($statistics, 'menuId'));

        // Charge tous les menus en une seule requête SQL
        $menus = $menuRepository->findBy([
            'id' => $menuIds,
        ]);

        // Indexe les menus par leur identifiant
        $menuMap = [];

        foreach ($menus as $menu) {
            $menuMap[$menu->getId()] = $menu;
        }

        // Construit la réponse finale
        $response = [];

        foreach ($statistics as $statistic) {
            $menu = $menuMap[$statistic['menuId']] ?? null;

            if (!$menu) {
                continue;
            }

            $response[] = [
                'menuId' => $menu->getId(),
                'menuTitle' => $menu->getTitle(),
                $valueField => $statistic[$valueField],
            ];
        }

        return $response;
    }
}
