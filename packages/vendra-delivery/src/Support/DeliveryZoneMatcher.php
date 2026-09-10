<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Support;

use Illuminate\Support\Facades\Config;
use Misaf\VendraDelivery\Data\DeliveryQuote;
use Misaf\VendraDelivery\Models\DeliveryZone;

/**
 * Prices a dropped pin against the tenant's delivery bands.
 *
 * This is a read: it decides nothing and records nothing, so it stays a plain
 * service rather than an action. Bands are consulted in `position` order —
 * tightest radius first — and the first one that still covers the point wins.
 */
final class DeliveryZoneMatcher
{
    public function quoteFor(float $latitude, float $longitude, ?string $currencyCode = null): DeliveryQuote
    {
        $zones = DeliveryZone::query()
            ->active()
            ->ordered()
            ->get();

        $nearestDistance = null;

        foreach ($zones as $zone) {
            $distance = $zone->distanceTo($latitude, $longitude);

            if ($nearestDistance === null || $distance < $nearestDistance) {
                $nearestDistance = $distance;
            }

            if (! $zone->covers($distance)) {
                continue;
            }

            return new DeliveryQuote(
                zone: $zone,
                distanceKm: round($distance, 3),
                feeAmount: $zone->requires_quote ? 0 : (int) $zone->fee_amount->getAmount(),
                currencyCode: $currencyCode ?? $zone->currency_code,
                requiresQuote: $zone->requires_quote,
            );
        }

        return DeliveryQuote::outOfRange(
            distanceKm: round($nearestDistance ?? 0.0, 3),
            currencyCode: $currencyCode ?? Config::string('app.currency', 'USD'),
        );
    }
}
