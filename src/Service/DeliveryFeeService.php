<?php

namespace App\Service;

class DeliveryFeeService
{
    private const BASE_DELIVERY_FEE = 5.00;

    private const PRICE_PER_KILOMETER = 0.59;

    /**
     * Calcule les frais de livraison.
     */
    public function calculate(
        float $distance,
        bool $isBordeaux
    ): float {

        if ($isBordeaux) {
            return self::BASE_DELIVERY_FEE;
        }

        return round(
            self::BASE_DELIVERY_FEE
            + ($distance * self::PRICE_PER_KILOMETER),
            2
        );
    }
}