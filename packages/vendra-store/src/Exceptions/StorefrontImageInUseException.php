<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Exceptions;

use DomainException;
use Misaf\VendraStore\Models\StorefrontImage;

/**
 * Deactivate the image instead.
 */
final class StorefrontImageInUseException extends DomainException
{
    public static function forImage(StorefrontImage $image): self
    {
        return new self(sprintf(
            'Storefront image [%s] cannot be deleted because deployments still use it.',
            $image->id,
        ));
    }
}
