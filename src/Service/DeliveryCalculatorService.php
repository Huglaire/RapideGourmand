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
     * à partir d'une adresse de livraison.
     */
    public function calculate(
        string $street,
        string $postalCode,
        string $city
    ): array {

        // Cas particulier : toute livraison dans Bordeaux
        // bénéficie du tarif fixe de 5 €.
        if (mb_strtolower(trim($city)) === 'bordeaux') {

            return [
                'distance' => 0,
                'deliveryFee' => $this->deliveryFeeService->calculate(
                    0,
                    true
                )
            ];
        }

        // Récupération des coordonnées GPS de l'adresse client.
        $coordinates = $this->geocodingService->geocode(
            $street,
            $postalCode,
            $city
        );

        // Calcul de la distance entre Rapide & Gourmand
        // (Bordeaux) et l'adresse de livraison.
        $distance = $this->distanceCalculator->calculate(
            $this->originLatitude,
            $this->originLongitude,
            $coordinates['lat'],
            $coordinates['lon']
        );

        // Calcul des frais de livraison à partir
        // de la distance obtenue.
        return [
            'distance' => $distance,
            'deliveryFee' => $this->deliveryFeeService->calculate(
                $distance,
                false
            )
        ];
    }
}