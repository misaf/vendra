<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Exceptions;

use DomainException;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;

final class InvalidStorefrontTransitionException extends DomainException
{
    public static function between(StorefrontDeploymentStatus $from, StorefrontDeploymentStatus $to): self
    {
        return new self(sprintf(
            'A storefront deployment cannot move from [%s] to [%s].',
            $from->value,
            $to->value,
        ));
    }
}
