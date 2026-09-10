<?php

declare(strict_types=1);

namespace Misaf\VendraWishlistApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;

final readonly class SavedItem
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The saved item unique identifier')]
        public int $id,
        public string $sellableType,
        public int $sellableId,
        public ?array $metadata,
        public string $savedAt,
    ) {}
}
