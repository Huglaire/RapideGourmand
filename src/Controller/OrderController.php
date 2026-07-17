<?php

namespace App\Controller;

use App\Service\StatisticsService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\Menu;
use App\Entity\Order;
use App\Entity\OrderMenu;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/orders')]
final class OrderController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'app_order_create', methods: ['POST'])]
    #[OA\Post(
        path: '/api/orders',
        summary: 'Créer une commande',
        description: 'Crée une nouvelle commande pour l’utilisateur connecté.',
        tags: ['Commandes'],
        security: [['Bearer' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'menuId',
                    'deliveryDate',
                    'guestNumber',
                    'deliveryStreet',
                    'deliveryPostalCode',
                    'deliveryCity'
                ],
                properties: [
                    new OA\Property(property: 'menuId', type: 'integer', example: 1),
                    new OA\Property(property: 'deliveryDate', type: 'string', format: 'date-time', example: '2026-08-15T12:00:00'),
                    new OA\Property(property: 'guestNumber', type: 'integer', example: 10),
                    new OA\Property(property: 'deliveryStreet', type: 'string', example: '12 avenue ciboulette'),
                    new OA\Property(property: 'deliveryPostalCode', type: 'string', example: '33000'),
                    new OA\Property(property: 'deliveryCity', type: 'string', example: 'Bordeaux')
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Commande créée avec succès.'),
            new OA\Response(response: 400, description: 'Requête invalide.'),
            new OA\Response(response: 401, description: 'Utilisateur non authentifié.'),
            new OA\Response(response: 404, description: 'Menu introuvable.')
        ]
    )]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        StatisticsService $statisticsService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        //Vérification des infos reçues
        if (
            !isset(
                $data['menuId'],
                $data['deliveryDate'],
                $data['guestNumber'],
                $data['deliveryStreet'],
                $data['deliveryPostalCode'],
                $data['deliveryCity']
            )
        ) {
            return new JsonResponse([
                'message' => 'Des informations obligatoires sont manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $menu = $entityManager->find(Menu::class, $data['menuId']);

        if (!$menu) {
            return new JsonResponse([
                'message' => 'Menu introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$menu->isAvailable()) {
            return new JsonResponse([
                'message' => 'Menu non disponible.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($menu->getStock() <= 0) {
            return new JsonResponse([
                'message' => 'Ce menu est momentanément indisponible.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Vérification de l'utilisateur connecté
        $user = $this->getUser();

        if (!$user instanceof User) {
            return new JsonResponse([
                'message' => 'Utilisateur non authentifié.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $order = new Order();

        $order->setUser($user);

        $now = new \DateTimeImmutable();

        $order->setOrderDate($now);

        //Vérification du format DateTime
        try {
            $deliveryDate = new \DateTimeImmutable($data['deliveryDate']);
        } catch (\Exception) {
            return new JsonResponse([
                'message' => 'Le format de la date de livraison est invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        //Vérification de la postériorité de la date de livraison par rapport à la date de commande
        if ($deliveryDate <= $now) {
            return new JsonResponse([
                'message' => 'La date de livraison doit être postérieure à la date actuelle.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $order->setDeliveryDate($deliveryDate);

        $order->setGuestNumber($data['guestNumber']);

        $order->setDeliveryStreet($data['deliveryStreet']);

        $order->setDeliveryPostalCode($data['deliveryPostalCode']);

        $order->setDeliveryCity($data['deliveryCity']);

        // Vérification du nombre minimum requis pour passer la commande
        if ($order->getGuestNumber() < $menu->getMinimumGuestNumber()) {
            return new JsonResponse([
                'message' => sprintf(
                    'Le nombre minimum de personnes pour ce menu est de %d.',
                    $menu->getMinimumGuestNumber()
                )
            ], Response::HTTP_BAD_REQUEST);
        }

        // Création de l'association entre la commande et le menu
        $orderMenu = new OrderMenu();

        $orderMenu->setCustomerOrder($order);
        $orderMenu->setMenu($menu);
        $orderMenu->setQuantity(1);
        $order->addOrderMenu($orderMenu);

        //Conversion en float pour ne pas avoir de string
        $totalPrice = (float) $menu->getPrice() * $order->getGuestNumber();

        // Réduction de 10 % si le nombre de personnes dépasse de 5 le minimum requis
        if (
            $order->getGuestNumber() >=
            $menu->getMinimumGuestNumber() + 5
        ) {
            $totalPrice *= 0.9;
        }

        // Frais de livraison
        $deliveryFee = 5.00;

        // le calcul kilométrique sera ajouté ultérieurement.

        $order->setDeliveryFee(number_format($deliveryFee, 2, '.', ''));

        $order->setTotalPrice(
            number_format($totalPrice + $deliveryFee, 2, '.', '')
        );

        //Décrémentation du stock du menu sélectionné
        $menu->setStock($menu->getStock() - 1);

        $entityManager->persist($order);
        $entityManager->persist($orderMenu);
        $entityManager->flush();

        // Synchronisation de la commande avec MongoDB afin d'alimenter
        // le module de statistiques.
        $statisticsService->recordOrder($order);

        return new JsonResponse([
            'message' => 'Commande créée avec succès.',
            'orderId' => $order->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'app_order_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        path: '/api/orders',
        summary: 'Lister ses commandes',
        description: 'Retourne la liste des commandes de l’utilisateur connecté.',
        tags: ['Commandes'],
        security: [['Bearer' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Liste des commandes récupérée avec succès.'
            ),
            new OA\Response(
                response: 401,
                description: 'Utilisateur non authentifié.'
            )
        ]
    )]
    public function index(OrderRepository $orderRepository): JsonResponse
    {
        $user = $this->getUser();

        $orders = $orderRepository->findBy(
            ['user' => $user],
            //Tri des commandes de la plus récente à la plus ancienne
            ['createdAt' => 'DESC']
        );

        $data = [];

        foreach ($orders as $order) {

            $menuTitle = null;

            if ($order->getOrderMenus()->count() > 0) {

                $menuTitle = $order
                    ->getOrderMenus()
                    ->first()
                    ->getMenu()
                    ->getTitle();
            }

            $data[] = [
                'id' => $order->getId(),
                'menuTitle' => $menuTitle,
                'deliveryDate' => $order->getDeliveryDate()->format('Y-m-d'),
                'guestNumber' => $order->getGuestNumber(),
                'deliveryStreet' => $order->getDeliveryStreet(),
                'deliveryPostalCode' => $order->getDeliveryPostalCode(),
                'deliveryCity' => $order->getDeliveryCity(),
                'totalPrice' => $order->getTotalPrice(),
                'deliveryFee' => $order->getDeliveryFee(),
                'status' => $order->getStatus(),
                'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', name: 'app_order_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        path: '/api/orders/{id}',
        summary: 'Consulter une commande par ID',
        description: 'Retourne le détail d’une commande appartenant à l’utilisateur connecté.',
        tags: ['Commandes'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant de la commande',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(response: 200, description: 'Commande récupérée avec succès.'),
            new OA\Response(response: 403, description: 'Accès interdit à cette commande.'),
            new OA\Response(response: 404, description: 'Commande introuvable.')
        ]
    )]
    public function show(Order $order): JsonResponse
    {
        // Symfony récupère automatiquement la commande correspondant à l'identifiant présent dans l'URL.
        // Si aucune commande n'est trouvée, une réponse HTTP 404 est renvoyée automatiquement.

        // Vérification que la commande appartient bien à l'utilisateur connecté.
        if ($order->getUser() !== $this->getUser()) {
            return $this->json([
                'message' => 'Accès interdit à cette commande.'
            ], Response::HTTP_FORBIDDEN);
        }

        $menus = [];

        foreach ($order->getOrderMenus() as $orderMenu) {
            $menus[] = [
                'id' => $orderMenu->getMenu()->getId(),
                'title' => $orderMenu->getMenu()->getTitle(),
                'quantity' => $orderMenu->getQuantity(),
                'unitPrice' => $orderMenu->getMenu()->getPrice(),
            ];
        }

        return $this->json([
            'id' => $order->getId(),
            'orderDate' => $order->getOrderDate()->format('Y-m-d H:i:s'),
            'deliveryDate' => $order->getDeliveryDate()->format('Y-m-d'),
            'guestNumber' => $order->getGuestNumber(),
            'deliveryStreet' => $order->getDeliveryStreet(),
            'deliveryPostalCode' => $order->getDeliveryPostalCode(),
            'deliveryCity' => $order->getDeliveryCity(),
            'deliveryFee' => $order->getDeliveryFee(),
            'totalPrice' => $order->getTotalPrice(),
            'equipmentBorrowed' => $order->isEquipmentBorrowed(),
            'status' => $order->getStatus(),
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $order->getUpdatedAt()?->format('Y-m-d H:i:s'),
            'menus' => $menus,
        ]);
    }

    #[Route('/{id}', name: 'app_order_update', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Patch(
        path: '/api/orders/{id}',
        summary: 'Modifier une commande par ID',
        description: 'Met à jour la date de livraison d’une commande en attente.',
        tags: ['Commandes'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant de la commande',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['deliveryDate'],
                properties: [
                    new OA\Property(
                        property: 'deliveryDate',
                        type: 'string',
                        format: 'date-time',
                        example: '2026-12-20T12:00:00'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Date de livraison mise à jour avec succès.'),
            new OA\Response(response: 400, description: 'Requête invalide.'),
            new OA\Response(response: 403, description: 'Accès interdit à cette commande.'),
            new OA\Response(response: 404, description: 'Commande introuvable.')
        ]
    )]
    public function update(
        Request $request,
        Order $order,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        // Vérification que la commande appartient bien à l'utilisateur connecté.
        if ($order->getUser() !== $this->getUser()) {
            return $this->json([
                'message' => 'Accès interdit à cette commande.'
            ], Response::HTTP_FORBIDDEN);
        }

        // Vérification que la commande est encore modifiable.
        if ($order->getStatus() !== Order::STATUS_PENDING) {
            return $this->json([
                'message' => 'Cette commande ne peut plus être modifiée.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'message' => 'Le corps de la requête doit contenir un JSON valide.'
            ], Response::HTTP_BAD_REQUEST);
        }
        // Règle métier : le client peut uniquement reporter la date de livraison.
        // Toute modification susceptible d'impacter la tarification ou l'organisation
        // de la prestation (menu, nombre d'invités, adresse...) doit être traitée par l'entreprise.

        if (
            count($data) !== 1 ||
            !array_key_exists('deliveryDate', $data)
        ) {
            return $this->json([
                'message' => 'Seule la date de livraison peut être modifiée.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $now = new \DateTimeImmutable();

        try {
            $deliveryDate = new \DateTimeImmutable($data['deliveryDate']);
        } catch (\Exception) {
            return $this->json([
                'message' => 'Le format de la date de livraison est invalide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($deliveryDate <= $now) {
            return $this->json([
                'message' => 'La date de livraison doit être postérieure à la date actuelle.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $order->setDeliveryDate($deliveryDate);
        $order->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->flush();

        return $this->json([
            'message' => 'La date de livraison a été mise à jour avec succès.'
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_order_cancel', methods: ['PATCH'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Patch(
        path: '/api/orders/{id}/cancel',
        summary: 'Annuler une commande par ID',
        description: 'Annule une commande en attente en enregistrant un motif d’annulation.',
        tags: ['Commandes'],
        security: [['Bearer' => []]],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                description: 'Identifiant de la commande',
                required: true,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['cancelReason'],
                properties: [
                    new OA\Property(
                        property: 'cancelReason',
                        type: 'string',
                        example: 'La mort de Mémé'
                    )
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Commande annulée avec succès.'),
            new OA\Response(response: 400, description: 'Requête invalide.'),
            new OA\Response(response: 403, description: 'Accès interdit à cette commande.'),
            new OA\Response(response: 404, description: 'Commande introuvable.')
        ]
    )]
    public function cancel(
        Request $request,
        Order $order,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        // Vérification que la commande appartient bien à l'utilisateur connecté.
        if ($order->getUser() !== $this->getUser()) {
            return $this->json([
                'message' => 'Accès interdit à cette commande.'
            ], Response::HTTP_FORBIDDEN);
        }

        //Impossibilité d'annuler une commande déjà annulée
        if ($order->getStatus() === Order::STATUS_CANCELLED) {
            return $this->json([
                'message' => 'Cette commande a déjà été annulée.'
            ], Response::HTTP_BAD_REQUEST);
        }


        // Règle métier : une commande ne peut être annulée que tant qu'elle est en attente.
        if ($order->getStatus() !== Order::STATUS_PENDING) {
            return $this->json([
                'message' => 'Cette commande a été validée et ne peut plus être annulée.'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'message' => 'Le corps de la requête doit contenir un JSON valide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Règle métier : le client doit obligatoirement indiquer un motif d'annulation.
        // Ce motif est conservé afin d'assurer un suivi des annulations.

        if (
            count($data) !== 1 ||
            !array_key_exists('cancelReason', $data)
        ) {
            return $this->json([
                'message' => 'Un motif d\'annulation est obligatoire.'
            ], Response::HTTP_BAD_REQUEST);
        }

        //Restriction de typage de l'entrée pour n'accepter que du type string
        if (!is_string($data['cancelReason'])) {
            return $this->json([
                'message' => 'La raison d\'annulation doit être une chaîne de caractères.'
            ], Response::HTTP_BAD_REQUEST);
        }

        //"trim" permet de refuser toute entrée composée uniquement d'espaces vides
        if (trim($data['cancelReason']) === '') {
            return $this->json([
                'message' => 'Le motif d\'annulation ne peut pas être vide.'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Enregistrement des informations relatives à l'annulation.
        $order->setCancelReason(trim($data['cancelReason']));
        $order->setCancelContactMethod('Site web');
        $order->setCancelDate(new \DateTimeImmutable());
        $order->setStatus(Order::STATUS_CANCELLED);
        $order->setUpdatedAt(new \DateTimeImmutable());

        // Restauration du stock des menus de la commande.
        foreach ($order->getOrderMenus() as $orderMenu) {
            $menu = $orderMenu->getMenu();

            $menu->setStock(
                $menu->getStock() + $orderMenu->getQuantity()
            );
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'La commande a été annulée avec succès.'
        ]);
    }
}
