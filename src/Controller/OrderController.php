<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Entity\Menu;
use App\Entity\Order;
use App\Entity\OrderMenu;
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
}
