<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Exceptions;

use DomainException;
use Misaf\VendraStore\Models\StorefrontImage;

/**
 * A storefront image was asked to be deleted while deployments still provision
 * from it. Deactivating it is the way to retire a build that is still in use.
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
