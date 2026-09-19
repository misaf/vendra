<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Support;

final class GeoDistance
{
    private const float EARTH_RADIUS_KM = 6371.0088;

    /**
     * Get the great-circle distance between two points in kilometers.
     *
     * The haversine formula is precise enough for bands a few kilometers wide.
     */
    public static function kilometres(
        float $fromLatitude,
        float $fromLongitude,
        float $toLatitude,
        float $toLongitude,
    ): float {
        $latitudeDelta = deg2rad($toLatitude - $fromLatitude);
        $longitudeDelta = deg2rad($toLongitude - $fromLongitude);

        $haversine = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($fromLatitude)) * cos(deg2rad($toLatitude)) * sin($longitudeDelta / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * asin(min(1.0, sqrt($haversine)));
    }
}
