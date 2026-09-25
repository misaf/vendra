<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use LogicException;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Events\ScheduledPlanChangeDropped;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Exceptions\SubscriptionPaymentException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Support\PlanCoverage;

final readonly class RenewSubscriptionAction
{
    public function __construct(
        private SubscribeAction $subscribeAction,
        private PlanCoverage $planCoverage,
    ) {}

    /**
     * Start the next period on the scheduled plan, or the same one.
     *
     * A period renewed within its grace window continues from where it ended,
     * so paying late loses no paid time; after the grace window the next
     * period starts now, as it does for a cancelled or unpaid one. An active
     * period cannot be renewed: activating the next one would cancel it before
     * the next one starts.
     *
     * @throws SubscriptionLimitException
     * @throws SubscriptionPaymentException
     */
    public function execute(Subscription $current): Subscription
    {
        throw_if($current->isActive(), LogicException::class, "Subscription [{$current->id}] is still running and cannot be renewed yet.");

        $subscriber = $current->subscriber()->firstOrFail();

        throw_unless($subscriber instanceof SubscriptionSubscriber, LogicException::class, "Subscription [{$current->id}] has unsupported subscriber type [{$current->subscriber_type}].");

        $plan = $this->planCoverage->renewalPlan($current);
        $scheduled = $current->scheduledPlan;

        if ($scheduled instanceof Plan && $plan !== $scheduled) {
            $current->update(['scheduled_plan_id' => null]);

            event(new ScheduledPlanChangeDropped($current, $scheduled));
        }

        throw_unless($plan instanceof Plan, LogicException::class, "Subscription [{$current->id}] has no plan to renew.");

        $startsAt = $current->ends_at?->isPast() === true && $current->suspendAt()?->isFuture() === true
            ? $current->ends_at->copy()
            : null;

        return $this->subscribeAction->execute(
            $subscriber,
            $plan,
            startsAt: $startsAt,
            autoRenews: $current->auto_renews,
        );
    }
}
