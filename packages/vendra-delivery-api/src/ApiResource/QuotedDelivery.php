<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;

/**
 * The priced answer to a dropped pin.
 */
final readonly class QuotedDelivery
{
    /**
     * @param  array<string, string>|null  $zoneName
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The quoted delivery identifier')]
        public string $id,
        public ?int $zoneId,
        public ?array $zoneName,
        public float $distanceKm,
        public int $feeAmount,
        public string $currencyCode,
        public bool $requiresQuote,
    ) {}
}
