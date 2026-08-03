<?php

namespace App\Service;

class DeliveryCalculatorService
{
    public function __construct(
        private readonly GeocodingService $geocodingService,
        private readonly DistanceCalculatorService $distanceCalculator,
        private readonly DeliveryFeeService $deliveryFeeService,
        private readonly float $originLatitude,
        private readonly float $originLongitude
    ) {
    }

    /**
     * Calcule les frais de livraison à partir d'une adresse.
     */
    public function calculate(
        string $street,
        string $postalCode,
        string $city
    ): float {

        // Cas particulier : Bordeaux
        if (mb_strtolower(trim($city)) === 'bordeaux') {
            return $this->deliveryFeeService->calculate(0, true);
        }

        $coordinates = $this->geocodingService->geocode(
            $street,
            $postalCode,
            $city
        );

        $distance = $this->distanceCalculator->calculate(
            $this->originLatitude,
            $this->originLongitude,
            $coordinates['lat'],
            $coordinates['lon']
        );

        return $this->deliveryFeeService->calculate(
            $distance,
            false
        );
    }
}