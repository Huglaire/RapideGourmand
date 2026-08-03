<?php

namespace App\Service;

class DistanceCalculatorService
{
    /**
     * Rayon moyen de la Terre (en kilomètres).
     */
    private const EARTH_RADIUS = 6371;

    /**
     * Calcule la distance entre deux points GPS grâce
     * à la formule de Haversine.
     */
    public function calculate(
        float $originLat,
        float $originLon,
        float $destinationLat,
        float $destinationLon
    ): float {

        $originLat = deg2rad($originLat);
        $originLon = deg2rad($originLon);

        $destinationLat = deg2rad($destinationLat);
        $destinationLon = deg2rad($destinationLon);

        $latDelta = $destinationLat - $originLat;
        $lonDelta = $destinationLon - $originLon;

        $a =
            sin($latDelta / 2) ** 2
            + cos($originLat)
            * cos($destinationLat)
            * sin($lonDelta / 2) ** 2;

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return round(
            self::EARTH_RADIUS * $c,
            2
        );
    }
}