<?php

declare(strict_types=1);

namespace Misaf\VendraCartApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;

final readonly class CartLine
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The cart line unique identifier')]
        public int $id,
        public string $sellableType,
        public int $sellableId,
        public int $quantity,
        public ?array $metadata,
    ) {}
}
