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
     * Calcule la distance et les frais de livraison
     * à partir d'une adresse.
     */
    public function calculate(
        string $street,
        string $postalCode,
        string $city
    ): array {

        // Cas particulier : Bordeaux
        if (mb_strtolower(trim($city)) === 'bordeaux') {

            return [
                'distance' => 0,
                'deliveryFee' => $this->deliveryFeeService->calculate(
                    0,
                    true
                )
            ];
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

        return [
            'distance' => $distance,
            'deliveryFee' => $this->deliveryFeeService->calculate(
                $distance,
                false
            )
        ];
    }
}