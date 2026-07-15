<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    /**
     * Retourne les menus disponibles.
     */
    public function findAvailableMenus(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.isAvailable = :available')
            ->setParameter('available', true)
            ->orderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Retourne les bornes du nombre minimum de personnes.
     */
    public function getGuestNumberRange(): array
    {
        $result = $this->createQueryBuilder('m')
            ->select(
                'MIN(m.minimumGuestNumber) AS minGuestNumber',
                'MAX(m.minimumGuestNumber) AS maxGuestNumber'
            )
            ->andWhere('m.isAvailable = :available')
            ->setParameter('available', true)
            ->getQuery()
            ->getSingleResult();

        return [
            'min' => (int) $result['minGuestNumber'],
            'max' => (int) $result['maxGuestNumber'],
        ];
    }

    /**
     * Retourne les bornes du prix.
     */
    public function getPriceRange(): array
    {
        $result = $this->createQueryBuilder('m')
            ->select(
                'MIN(m.price) AS minPrice',
                'MAX(m.price) AS maxPrice'
            )
            ->andWhere('m.isAvailable = :available')
            ->setParameter('available', true)
            ->getQuery()
            ->getSingleResult();

        return [
            'min' => (float) $result['minPrice'],
            'max' => (float) $result['maxPrice'],
        ];
    }

    /**
     * Retourne les menus correspondant aux filtres sélectionnés.
     */
    public function findFilteredMenus(array $filters): array
    {
        // Jointure des entités liées (alias : m = Menu, t = Theme, d = Diet, p = Picture)
        $queryBuilder = $this->createQueryBuilder('m')
            ->leftJoin('m.theme', 't')
            ->addSelect('t')
            ->leftJoin('m.diets', 'd')
            ->addSelect('d')
            ->leftJoin('m.pictures', 'p')
            ->addSelect('p')
            ->andWhere('m.isAvailable = :available')
            ->setParameter('available', true);

        if (!empty($filters['themes'])) {
            $queryBuilder
                ->andWhere('t.id IN (:themes)')
                ->setParameter('themes', $filters['themes']);
        }

        if (!empty($filters['diets'])) {
            $queryBuilder
                ->andWhere('d.id IN (:diets)')
                ->setParameter('diets', $filters['diets']);
        }

        if (!empty($filters['guestNumber'])) {
            $queryBuilder
                ->andWhere('m.minimumGuestNumber <= :guestNumber')
                ->setParameter('guestNumber', $filters['guestNumber']);
        }

        if (!empty($filters['price'])) {
            $queryBuilder
                ->andWhere('m.price <= :price')
                ->setParameter('price', $filters['price']);
        }

        return $queryBuilder
            ->orderBy('m.createdAt', 'DESC')
            ->distinct()
            ->getQuery()
            ->getResult();
    }
}
