<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Models\StorefrontImage;

final class UpdateStorefrontImageAction
{
    /**
     * Updates a catalog entry. Deactivating an image only removes it from new
     * storefront selection; deployments already using it keep provisioning from it.
     *
     * @param  array{image?: string, notes?: string|null, active?: bool}  $attributes
     */
    public function execute(StorefrontImage $image, array $attributes): StorefrontImage
    {
        $image->update($attributes);

        return $image;
    }
}
