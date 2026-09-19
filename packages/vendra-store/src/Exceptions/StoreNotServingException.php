<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Exceptions;

use DomainException;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Reactivate or restore the store instead.
 */
final class StoreNotServingException extends DomainException
{
    public static function forDeployment(StorefrontDeployment $deployment): self
    {
        return new self(sprintf(
            'Store [%s] is suspended or offboarded, so its storefront cannot be started.',
            $deployment->store_id,
        ));
    }
}
