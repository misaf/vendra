<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Models\StorefrontImage;

final class CreateStorefrontImageAction
{
    /**
     * @param  array{image: string, notes?: string|null, active?: bool}  $attributes
     */
    public function execute(array $attributes): StorefrontImage
    {
        return StorefrontImage::query()->create($attributes);
    }
}
