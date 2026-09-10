<?php

declare(strict_types=1);

namespace Misaf\VendraOrderApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;

final readonly class OrderLine
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The order line unique identifier')]
        public int $id,
        public string $sellableType,
        public int $sellableId,
        public string $name,
        public int $quantity,
        public int $unitAmount,
        public int $lineAmount,
        public ?array $metadata,
    ) {}
}
