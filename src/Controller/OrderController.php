<?php

namespace App\Controller;

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

#[Route('/api/orders')]
final class OrderController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('', name: 'app_order_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager
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

        return new JsonResponse([
            'message' => 'Commande créée avec succès.',
            'orderId' => $order->getId()
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'app_order_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
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
            $data[] = [
                'id' => $order->getId(),
                'deliveryDate' => $order->getDeliveryDate()->format('Y-m-d'),
                'guestNumber' => $order->getGuestNumber(),
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
}
