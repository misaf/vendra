<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Exceptions\StorefrontImageInUseException;
use Misaf\VendraStore\Models\StorefrontImage;

final class DeleteStorefrontImageAction
{
    /**
     * @throws StorefrontImageInUseException
     */
    public function execute(StorefrontImage $image): void
    {
        DB::transaction(function () use ($image): void {
            $lockedImage = $image->refreshForUpdate();

            if ($lockedImage->isInUse()) {
                throw StorefrontImageInUseException::forImage($lockedImage);
            }

            $lockedImage->delete();
        });
    }
}
