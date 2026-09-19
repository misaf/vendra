<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Observers;

use Misaf\VendraSubscription\Exceptions\PlanInUseException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSupport\Observers\Concerns\MaintainsSingleActiveDefault;

final class PlanObserver
{
    use MaintainsSingleActiveDefault;

    /**
     * Abort deleting a plan that still backs a subscription.
     */
    public function deleting(Plan $plan): void
    {
        if ($plan->isInUse()) {
            throw PlanInUseException::forPlan($plan);
        }
    }
}
