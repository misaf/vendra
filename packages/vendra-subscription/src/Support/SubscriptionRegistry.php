<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

/**
 * Keeps subscription orchestration off the subscriber models.
 */
final class SubscriptionRegistry
{
    /**
     * Lock the subscriber's row to serialize concurrent billing operations.
     *
     * @template TSubscriber of Model&SubscriptionSubscriber
     *
     * @param  TSubscriber  $subscriber
     * @return TSubscriber
     */
    public function lockSubscriber(Model&SubscriptionSubscriber $subscriber): Model&SubscriptionSubscriber
    {
        $subscriber->refreshForUpdate();

        throw_if(method_exists($subscriber, 'trashed') && $subscriber->trashed(), (new ModelNotFoundException)->setModel($subscriber::class));

        return $subscriber;
    }

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
     * Cancel the subscriber's open subscriptions and their pending payments.
     *
     * A pending payment left behind would still be charged by payment recovery.
     */
    public function cancelOpen(Model&SubscriptionSubscriber $subscriber): int
    {
        $this->lockOpenPayments($subscriber)->each->cancel();

        return $this->subscriptionsQuery($subscriber)
            ->whereIn('status', SubscriptionStatus::cancellable())
            ->update(['status' => SubscriptionStatus::Cancelled->value]);
    }

    /**
     * @param  array<int, int>  $keys
     */
    public function cancelPending(Model&SubscriptionSubscriber $subscriber, array $keys): int
    {
        return $this->subscriptionsQuery($subscriber)
            ->whereKey($keys)
            ->where('status', SubscriptionStatus::PendingPayment->value)
            ->update(['status' => SubscriptionStatus::Cancelled->value]);
    }

    /**
     * @return Collection<int, SubscriptionPayment>
     */
    public function lockOpenPayments(Model&SubscriptionSubscriber $subscriber): Collection
    {
        return SubscriptionPayment::query()
            ->whereIn('subscription_id', $this->subscriptionsQuery($subscriber)->select('id'))
            ->open()
            ->lockForUpdate()
            ->get();
    }

    public function reassignOpenPayments(Model&SubscriptionSubscriber $subscriber, Model $payer): int
    {
        $payments = $this->lockOpenPayments($subscriber);

        $payments->each(function (SubscriptionPayment $payment) use ($payer): void {
            $payment->payer()->associate($payer);
            $payment->save();
        });

        return $payments->count();
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
