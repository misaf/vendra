<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Data;

use Misaf\VendraDelivery\Models\DeliveryZone;

/**
 * `requiresQuote` marks an address priced by hand, which checkout must refuse.
 */
final readonly class DeliveryQuote
{
    public function __construct(
        public ?DeliveryZone $zone,
        public float $distanceKm,
        public int $feeAmount,
        public string $currencyCode,
        public bool $requiresQuote,
    ) {}

    /**
     * Create the quote for a point no band covers.
     */
    public static function outOfRange(float $distanceKm, string $currencyCode): self
    {
        return new self(
            zone: null,
            distanceKm: $distanceKm,
            feeAmount: 0,
            currencyCode: $currencyCode,
            requiresQuote: true,
        );
    }

    public function isDeliverable(): bool
    {
        return ! $this->requiresQuote;
    }
}
