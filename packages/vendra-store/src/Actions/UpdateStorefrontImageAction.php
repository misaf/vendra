<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Models\StorefrontImage;

final class UpdateStorefrontImageAction
{
    /**
     * Deactivating an image hides it from new stores; existing deployments keep it.
     *
     * @param  array{image?: string, notes?: string|null, active?: bool}  $attributes
     */
    public function execute(StorefrontImage $image, array $attributes): StorefrontImage
    {
        $image->update($attributes);

        return $image;
    }
}
