<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Contracts\PlanUsageGuard;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Models\Plan;

final class NullPlanUsageGuard implements PlanUsageGuard
{
    public function assertPlanCovers(Model&SubscriptionSubscriber $subscriber, Plan $plan): void {}
}
