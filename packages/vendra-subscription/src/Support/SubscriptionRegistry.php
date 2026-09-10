<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

/**
 * Owns the write and locking operations on a subscriber's subscriptions and
 * payments. The engine performs these against its own {@see Subscription} and
 * {@see SubscriptionPayment} models keyed by the subscriber's polymorphic
 * identity, so subscribers only expose intent-level capabilities and never carry
 * subscription orchestration on their models.
 */
final class SubscriptionRegistry
{
    /**
     * Re-fetch the subscriber under a row lock so a subscription transaction can
     * serialize concurrent billing operations for it.
     *
     * @template TSubscriber of Model&SubscriptionSubscriber
     *
     * @param  TSubscriber  $subscriber
     * @return TSubscriber
     */
    public function lockSubscriber(Model&SubscriptionSubscriber $subscriber): Model&SubscriptionSubscriber
    {
        /** @var TSubscriber */
        return $subscriber->newQuery()
            ->whereKey($subscriber->getKey())
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Create a subscription period owned by the subscriber.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function create(Model&SubscriptionSubscriber $subscriber, array $attributes): Subscription
    {
        $subscription = new Subscription($attributes);
        $subscription->subscriber()->associate($subscriber);
        $subscription->save();

        return $subscription;
    }

    /**
     * Cancel the subscriber's active subscriptions, optionally keeping one.
     *
     * @return int the number of subscriptions cancelled
     */
    public function cancelActive(Model&SubscriptionSubscriber $subscriber, ?int $exceptKey = null): int
    {
        $query = $this->subscriptionsQuery($subscriber)
            ->where('status', SubscriptionStatus::Active->value);

        if ($exceptKey !== null) {
            $query->whereKeyNot($exceptKey);
        }

        return $query->update(['status' => SubscriptionStatus::Cancelled->value]);
    }

    /**
     * Cancel every non-terminal subscription held by a subscriber.
     *
     * @return int the number of subscriptions cancelled
     */
    public function cancelOpen(Model&SubscriptionSubscriber $subscriber): int
    {
        return $this->subscriptionsQuery($subscriber)
            ->whereIn('status', [
                SubscriptionStatus::PendingPayment->value,
                SubscriptionStatus::Active->value,
                SubscriptionStatus::PastDue->value,
            ])
            ->update(['status' => SubscriptionStatus::Cancelled->value]);
    }

    /**
     * Cancel the subscriber's pending-payment subscriptions with the given keys.
     *
     * @param  array<int, int>  $keys
     * @return int the number of subscriptions cancelled
     */
    public function cancelPending(Model&SubscriptionSubscriber $subscriber, array $keys): int
    {
        return $this->subscriptionsQuery($subscriber)
            ->whereKey($keys)
            ->where('status', SubscriptionStatus::PendingPayment->value)
            ->update(['status' => SubscriptionStatus::Cancelled->value]);
    }

    /**
     * Lock and return the subscriber's open (non-terminal) subscription payments.
     *
     * @return Collection<int, SubscriptionPayment>
     */
    public function lockOpenPayments(Model&SubscriptionSubscriber $subscriber): Collection
    {
        return SubscriptionPayment::query()
            ->whereIn('subscription_id', $this->subscriptionsQuery($subscriber)->select('id'))
            ->whereIn('status', [
                SubscriptionPaymentStatus::Pending,
                SubscriptionPaymentStatus::Processing,
                SubscriptionPaymentStatus::RequiresAction,
                SubscriptionPaymentStatus::NeedsReconciliation,
            ])
            ->lockForUpdate()
            ->get();
    }

    /**
     * @return Builder<Subscription>
     */
    private function subscriptionsQuery(Model&SubscriptionSubscriber $subscriber): Builder
    {
        return Subscription::query()
            ->where('subscriber_type', $subscriber->getMorphClass())
            ->where('subscriber_id', $subscriber->getKey());
    }
}
