<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionActivated;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSubscription\Support\SubscriptionRegistry;

final class ActivateSubscriptionAction
{
    public function __construct(private readonly SubscriptionRegistry $subscriptionRegistry) {}

    /**
     * Activate the subscription of a paid payment: supersede the subscriber's
     * other active subscriptions and reactivate its units atomically, then
     * raise SubscriptionActivated for host-specific side effects. A paid payment
     * for a subscriber that does not implement SubscriptionSubscriber is a bug
     * rather than a no-op, so it fails loud instead of silently never activating.
     */
    public function execute(SubscriptionPayment $payment): void
    {
        $paymentId = $payment->id;
        $activated = DB::transaction(function () use ($paymentId): ?Subscription {
            $payment = SubscriptionPayment::query()->whereKey($paymentId)->firstOrFail();
            $subscription = $payment->subscription()->firstOrFail();
            $subscriber = $subscription->subscriber()->firstOrFail();

            if (! $subscriber instanceof SubscriptionSubscriber) {
                throw new LogicException("Subscription [{$subscription->id}] has unsupported subscriber type [{$subscription->subscriber_type}]; subscribers must implement SubscriptionSubscriber to be activated.");
            }

            $lockedSubscriber = $this->subscriptionRegistry->lockSubscriber($subscriber);
            $lockedPayment = SubscriptionPayment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();
            $lockedSubscription = Subscription::query()
                ->whereKey($subscription->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedPayment->status !== SubscriptionPaymentStatus::Paid
                || $lockedSubscription->status !== SubscriptionStatus::PendingPayment) {
                return null;
            }

            $this->subscriptionRegistry->cancelActive($lockedSubscriber, $lockedSubscription->id);
            $lockedSubscription->activate();
            $lockedSubscriber->reactivateSuspendedUnits();

            return $lockedSubscription;
        }, attempts: 5);

        if ($activated instanceof Subscription) {
            SubscriptionActivated::dispatch($activated);
        }
    }
}
