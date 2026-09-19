<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Contracts;

use Misaf\VendraSupport\Data\SubscriptionCharge;
use Misaf\VendraSupport\Data\SubscriptionChargeResult;

/**
 * Without a bound provider, the null default reports itself unavailable.
 */
interface SubscriptionCharger
{
    /**
     * Get the provider's stable name, without any network I/O.
     */
    public function provider(): string;

    public function available(): bool;

    /**
     * Start or resume collecting the payment.
     *
     * Must be idempotent by reference, and reject a reused reference with different details.
     */
    public function charge(SubscriptionCharge $charge): SubscriptionChargeResult;

    public function retrieve(SubscriptionCharge $charge): SubscriptionChargeResult;
}
