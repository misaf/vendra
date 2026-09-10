<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;

final readonly class DeliverySlot
{
    /**
     * @param  array<string, string>  $name
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The delivery window unique identifier')]
        public int $id,
        public array $name,
        public string $startsAt,
        public string $endsAt,
    ) {}
}
