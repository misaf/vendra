<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Exceptions;

use DomainException;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * A storefront was asked to run while its store is suspended or offboarded.
 *
 * Suspension and offboarding record a Stopped intent; starting, restarting or
 * redeploying the storefront would override that decision while the store still
 * reads as down. Reactivating or restoring the store is the way back.
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
