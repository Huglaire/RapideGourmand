<?php

namespace App\Service;

use App\Document\OrderStatistic;
use Doctrine\ODM\MongoDB\DocumentManager;

class StatisticsQueryService
{
    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
    }

    public function getOrdersPerMenu(): array
    {
        // Création du pipeline d'agrégation sur la collection OrderStatistic
        $builder = $this->documentManager
            ->createAggregationBuilder(OrderStatistic::class);

        $builder
            // Regroupe les commandes par menu
            ->group()
            ->field('_id')
            ->expression('$menuId')

            // Compte le nombre de commandes par menu
            ->field('orderCount')
            ->sum(1)

            // Trie du menu le plus commandé au moins commandé
            ->sort([
                'orderCount' => -1,
            ])

            // Met en forme le résultat retourné par MongoDB
            ->project()
            ->excludeFields(['_id'])
            ->includeFields(['orderCount'])
            ->field('menuId')
            ->expression('$_id');

        // Exécute le pipeline et retourne le résultat sous forme de tableau PHP
        return $builder
            ->execute()
            ->toArray();
    }

    public function getRevenueByMenu(
        ?int $menuId = null,
        ?\DateTimeInterface $start = null,
        ?\DateTimeInterface $end = null,
    ): array {

        // Création du pipeline d'agrégation sur la collection OrderStatistic
        $builder = $this->documentManager
            ->createAggregationBuilder(OrderStatistic::class);

        // Ajoute les filtres uniquement si nécessaire
        if ($menuId !== null || $start !== null || $end !== null) {

            $match = $builder->match();

            // Filtre sur un menu précis
            if ($menuId !== null) {
                $match
                    ->field('menuId')
                    ->equals($menuId);
            }

            // Filtre à partir d'une date de début
            if ($start !== null) {
                $match
                    ->field('createdAt')
                    ->gte($start);
            }

            // Filtre jusqu'à une date de fin
            if ($end !== null) {
                $match
                    ->field('createdAt')
                    ->lte($end);
            }
        }

        $builder
            // Regroupe les commandes par menu
            ->group()
            ->field('_id')
            ->expression('$menuId')

            // Additionne le chiffre d'affaires de chaque menu
            ->field('revenue')
            ->sum('$totalPrice')

            // Trie du chiffre d'affaires le plus élevé au plus faible
            ->sort([
                'revenue' => -1,
            ])

            // Met en forme le résultat retourné par MongoDB
            ->project()
            ->excludeFields(['_id'])
            ->includeFields(['revenue'])
            ->field('menuId')
            ->expression('$_id');

        // Exécute le pipeline et retourne le résultat sous forme de tableau PHP
        return $builder
            ->execute()
            ->toArray();
    }
}