<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/employee/orders')]
#[IsGranted('ROLE_EMPLOYEE')]
final class EmployeeOrderController extends AbstractController
{
    #[Route('', name: 'employee_orders_index', methods: ['GET'])]
    #[OA\Get(
        path: '/api/employee/orders',
        summary: 'Lister les commandes',
        tags: ['Commandes']
    )]
    public function index(
        Request $request,
        OrderRepository $orderRepository
    ): JsonResponse {

        $status = $request->query->get('status');
        $customer = $request->query->get('customer');

        $orders = $orderRepository->findEmployeeOrders(
            $status,
            $customer
        );

        $data = [];

        foreach ($orders as $order) {

            $menuTitle = null;

            if (!$order->getOrderMenus()->isEmpty()) {

                $orderMenu = $order->getOrderMenus()->first();

                if ($orderMenu && $orderMenu->getMenu()) {
                    $menuTitle = $orderMenu->getMenu()->getTitle();
                }
            }

            $data[] = [
                'id' => $order->getId(),
                'customer' => sprintf(
                    '%s %s',
                    $order->getUser()->getFirstName(),
                    $order->getUser()->getLastName()
                ),
                'menuTitle' => $menuTitle,
                'deliveryDate' => $order->getDeliveryDate()->format('Y-m-d'),
                'guestNumber' => $order->getGuestNumber(),
                'deliveryStreet' => $order->getDeliveryStreet(),
                'deliveryPostalCode' => $order->getDeliveryPostalCode(),
                'deliveryCity' => $order->getDeliveryCity(),
                'totalPrice' => $order->getTotalPrice(),
                'status' => $order->getStatus(),
            ];
        }

        return $this->json($data);
    }


    #[Route('/{id}', name: 'api_employee_orders_show', methods: ['GET'])]
    #[OA\Get(
        path: '/api/employee/orders/{id}',
        summary: 'Afficher le détail d’une commande',
        tags: ['Commandes']
    )]
    public function show(Order $order): JsonResponse
    {
        $menuTitle = null;

        if ($order->getOrderMenus()->count() > 0) {

            $menuTitle = $order
                ->getOrderMenus()
                ->first()
                ->getMenu()
                ->getTitle();
        }

        return $this->json([
            'id' => $order->getId(),
            'customer' => sprintf(
                '%s %s',
                $order->getUser()->getFirstName(),
                $order->getUser()->getLastName()
            ),
            'email' => $order->getUser()->getEmail(),
            'menuTitle' => $menuTitle,
            'deliveryDate' => $order->getDeliveryDate()->format('Y-m-d'),
            'guestNumber' => $order->getGuestNumber(),
            'deliveryStreet' => $order->getDeliveryStreet(),
            'deliveryPostalCode' => $order->getDeliveryPostalCode(),
            'deliveryCity' => $order->getDeliveryCity(),
            'deliveryFee' => $order->getDeliveryFee(),
            'totalPrice' => $order->getTotalPrice(),
            'status' => $order->getStatus(),
            'cancelReason' => $order->getCancelReason(),
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s')
        ]);
    }


    #[Route('/{id}/status', name: 'api_employee_orders_update_status', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/employee/orders/{id}/status',
        summary: 'Mettre à jour le statut d’une commande',
        tags: ['Commandes']
    )]
    public function updateStatus(
        Request $request,
        Order $order,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = json_decode(
            $request->getContent(),
            true
        );

        $status = $data['status'] ?? null;

        $allowedStatuses = [
            Order::STATUS_ACCEPTED,
            Order::STATUS_PREPARING,
            Order::STATUS_DELIVERING,
            Order::STATUS_DELIVERED,
            Order::STATUS_WAITING_EQUIPMENT,
            Order::STATUS_FINISHED,
            Order::STATUS_CANCELLED
        ];

        if (!in_array($status, $allowedStatuses, true)) {

            return $this->json(
                [
                    'message' => 'Statut invalide.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $order->setStatus($status);

        $entityManager->flush();

        return $this->json([
            'message' => 'Statut mis à jour.',
            'status' => $order->getStatus()
        ]);
    }


    #[Route('/{id}/cancel', name: 'api_employee_orders_cancel', methods: ['PATCH'])]
    #[OA\Patch(
        path: '/api/employee/orders/{id}/cancel',
        summary: 'Annuler une commande',
        tags: ['Commandes']
    )]
    public function cancel(
        Request $request,
        Order $order,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = json_decode(
            $request->getContent(),
            true
        );

        $reason = trim(
            $data['reason'] ?? ''
        );

        if ($reason === '') {

            return $this->json(
                [
                    'message' => 'Le motif est obligatoire.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $order->setStatus(
            Order::STATUS_CANCELLED
        );

        $order->setCancelReason(
            $reason
        );

        $order->setCancelDate(
            new \DateTimeImmutable()
        );

        $order->setUpdatedAt(
            new \DateTimeImmutable()
        );

        $entityManager->flush();

        return $this->json([
            'message' => 'Commande annulée.',
            'status' => $order->getStatus()
        ]);
    }
}