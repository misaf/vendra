<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Support\PlanChangeQuote;
use Misaf\VendraSubscription\Support\PlanCoverage;
use Misaf\VendraSubscription\Support\SubscriptionRegistry;

final readonly class ChangeSubscriptionPlanAction
{
    public function __construct(
        private SubscribeAction $subscribeAction,
        private SubscriptionRegistry $subscriptionRegistry,
        private PlanCoverage $planCoverage,
    ) {}

    /**
     * Upgrades start now; downgrades are scheduled for the next renewal.
     *
     * Choosing the current plan drops a scheduled change.
     *
     * @param  Model&SubscriptionSubscriber  $subscriber
     *
     * @throws SubscriptionLimitException
     */
    public function execute(SubscriptionSubscriber $subscriber, Plan $plan): Subscription
    {
        $current = $subscriber->activeSubscription();

        if ($current instanceof Subscription && $current->plan_id === $plan->id) {
            $current->update(['scheduled_plan_id' => null]);

            return $current;
        }

        $quote = PlanChangeQuote::for($current, $plan);

        if ($quote->appliesNow || ! $current instanceof Subscription) {
            return $this->subscribeAction->execute(
                $subscriber,
                $plan,
                endsAt: $quote->endsAt,
                amount: $quote->amount,
                autoRenews: $current->auto_renews ?? true,
            );
        }

        return DB::transaction(function () use ($subscriber, $plan, $current): Subscription {
            $lockedSubscriber = $this->subscriptionRegistry->lockSubscriber($subscriber);

            $this->planCoverage->assertCovers($lockedSubscriber, $plan);

            $current->refreshForUpdate()->update(['scheduled_plan_id' => $plan->id]);

            return $current;
        });
    }
}
