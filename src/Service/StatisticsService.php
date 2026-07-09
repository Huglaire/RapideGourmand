<?php

namespace App\Service;

use Doctrine\ODM\MongoDB\DocumentManager;
use App\Document\OrderStatistic;
use App\Entity\Order;

class StatisticsService
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {}

    public function recordOrder(Order $order): void
    {
        // Une commande ne contient actuellement qu'un seul menu.
        // L'utilisation de l'entité OrderMenu permet toutefois de conserver
        // une architecture évolutive si plusieurs menus sont proposés à l'avenir.
        $orderMenu = $order->getOrderMenus()->first();

        if (!$orderMenu) {
            return;
        }

        $statistic = new OrderStatistic();

        $statistic->setOrderId($order->getId());
        $statistic->setMenuId($orderMenu->getMenu()->getId());
        $statistic->setDeliveryDate(
            \DateTime::createFromImmutable($order->getDeliveryDate())
        );
        $statistic->setGuestNumber($order->getGuestNumber());
        $statistic->setTotalPrice((float) $order->getTotalPrice());
        $statistic->setCancelled(false);


        $this->documentManager->persist($statistic);

        $this->documentManager->flush();
    }
}
