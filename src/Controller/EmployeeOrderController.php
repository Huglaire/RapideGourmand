<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/employee/orders')]
#[IsGranted('ROLE_EMPLOYEE')]
final class EmployeeOrderController extends AbstractController
{
    #[Route('', name: 'employee_orders_index', methods: ['GET'])]
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
}
