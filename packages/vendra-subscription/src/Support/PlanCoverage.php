<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Contracts\PlanUsageGuard;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * Decide whether a plan still fits what a subscriber already uses: its units and,
 * through the bound {@see PlanUsageGuard}, the features and limits of each unit.
 */
final readonly class PlanCoverage
{
    public function __construct(private PlanUsageGuard $planUsageGuard) {}

    /**
     * Callers that act on the answer run it after locking the subscriber row.
     *
     * @throws SubscriptionLimitException
     */
    public function assertCovers(Model&SubscriptionSubscriber $subscriber, Plan $plan): void
    {
        $currentUnits = $subscriber->subscribedUnitCount();

        if ($currentUnits > $plan->max_units) {
            throw SubscriptionLimitException::planBelowUsage($subscriber, $plan->max_units, $currentUnits);
        }

        $this->planUsageGuard->assertPlanCovers($subscriber, $plan);
    }

    /**
     * Answer without locking, so a plan picker can flag plans ahead of time.
     */
    public function covers(Model&SubscriptionSubscriber $subscriber, Plan $plan): bool
    {
        try {
            $this->assertCovers($subscriber, $plan);
        } catch (SubscriptionLimitException) {
            return false;
        }

        return true;
    }

    /**
     * The plan a renewal of this period starts: the scheduled one while it still
     * fits the subscriber, otherwise the current one.
     */
    public function renewalPlan(Subscription $current): ?Plan
    {
        $scheduled = $current->scheduledPlan;
        $subscriber = $current->subscriber;

        if ($scheduled instanceof Plan && $subscriber instanceof SubscriptionSubscriber && $this->covers($subscriber, $scheduled)) {
            return $scheduled;
        }

        return $current->plan;
    }

    public function scheduledPlanOutgrown(Subscription $current): bool
    {
        return $current->scheduledPlan instanceof Plan && $this->renewalPlan($current) !== $current->scheduledPlan;
    }
}
