<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Exceptions;

use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Enums\PlanLimit;
use RuntimeException;

/**
 * The message is translated, so panels can show it as it is.
 */
final class EntitlementExceededException extends RuntimeException
{
    public static function featureUnavailable(PlanFeature $feature): self
    {
        return new self(__('vendra-support::entitlements.feature_unavailable', [
            'feature' => $feature->getLabel(),
        ]));
    }

    public static function limitReached(PlanLimit $limit, int $allowed): self
    {
        return new self(__('vendra-support::entitlements.limit_reached', [
            'limit' => $limit->getLabel(),
            'allowed' => $allowed,
        ]));
    }
}
