<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Contracts;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;

/**
 * Refuse a plan whose features or limits fall short of what the subscriber uses.
 *
 * The package that owns the units binds it; the null default refuses nothing.
 * It runs inside the caller's transaction, after the subscriber row is locked.
 */
interface PlanUsageGuard
{
    /**
     * @throws SubscriptionLimitException
     */
    public function assertPlanCovers(Model&SubscriptionSubscriber $subscriber, Plan $plan): void;
}
