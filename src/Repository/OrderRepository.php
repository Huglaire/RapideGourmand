<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * Retourne les commandes en appliquant les filtres éventuels.
     *
     * @return Order[]
     */
    public function findEmployeeOrders(
        ?string $status = null,
        ?string $customer = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('o')
            ->leftJoin('o.user', 'u')
            ->addSelect('u')
            ->orderBy('o.deliveryDate', 'ASC');

        if ($status) {
            $queryBuilder
                ->andWhere('o.status = :status')
                ->setParameter('status', $status);
        }

        if ($customer) {
            $queryBuilder
                ->andWhere(
                    'LOWER(u.firstName) LIKE :customer
                    OR LOWER(u.lastName) LIKE :customer'
                )
                ->setParameter(
                    'customer',
                    '%' . mb_strtolower($customer) . '%'
                );
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}