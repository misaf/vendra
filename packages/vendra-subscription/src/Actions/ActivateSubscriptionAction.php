<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Events\SubscriptionActivated;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;
use Misaf\VendraSubscription\Support\SubscriptionRegistry;

final readonly class ActivateSubscriptionAction
{
    public function __construct(private SubscriptionRegistry $subscriptionRegistry) {}

    /**
     * A subscriber that is not a `SubscriptionSubscriber` throws rather than
     * silently never activating.
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
            $lockedPayment = $payment->refreshForUpdate();
            $lockedSubscription = $subscription->refreshForUpdate();

            throw_if($lockedSubscription->trashed(), (new ModelNotFoundException)->setModel(Subscription::class));

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
            event(new SubscriptionActivated($activated));
        }
    }
}
