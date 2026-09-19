<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Contracts;

use Illuminate\Database\Eloquent\Model;

/**
 * Suspend and reactivate the units a subscriber's plan pays for.
 *
 * The package that owns the units binds it; the null default touches nothing.
 * Both methods run inside the caller's transaction and return the unit count.
 */
interface SubscriptionUnitSuspender
{
    public function suspendActiveUnits(Model&SubscriptionSubscriber $subscriber): int;

    public function reactivateSuspendedUnits(Model&SubscriptionSubscriber $subscriber): int;
}
