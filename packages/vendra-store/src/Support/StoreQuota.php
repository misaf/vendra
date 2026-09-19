<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;

final class StoreQuota
{
    /**
     * @param  Model&SubscriptionSubscriber  $subscriber
     */
    public function canCreateStore(SubscriptionSubscriber $subscriber): bool
    {
        return $this->remainingStores($subscriber) > 0;
    }

    /**
     * @param  Model&SubscriptionSubscriber  $subscriber
     */
    public function remainingStores(SubscriptionSubscriber $subscriber): int
    {
        if (! $subscriber->canHoldUnits()) {
            return 0;
        }

        $plan = $subscriber->activeSubscription()?->plan;

        if ($plan === null) {
            return 0;
        }

        return max(0, $plan->max_units - $subscriber->subscribedUnitCount());
    }

    /**
     * @param  Model&SubscriptionSubscriber  $subscriber
     *
     * @throws SubscriptionLimitException
     */
    public function assertCanCreateStore(SubscriptionSubscriber $subscriber): void
    {
        if (! $subscriber->canHoldUnits()) {
            throw SubscriptionLimitException::subscriberInactive($subscriber);
        }

        $plan = $subscriber->activeSubscription()?->plan;

        if ($plan === null) {
            throw SubscriptionLimitException::noActiveSubscription($subscriber);
        }

        if ($subscriber->subscribedUnitCount() >= $plan->max_units) {
            throw SubscriptionLimitException::unitQuotaReached($subscriber, $plan->max_units);
        }
    }

    /**
     * Lock the subscriber's row and assert it may create another store.
     *
     * The lock stops two concurrent claims from taking the last slot. Call
     * inside the transaction that writes the store.
     *
     * @template TSubscriber of Model&SubscriptionSubscriber
     *
     * @param  TSubscriber  $subscriber
     * @return TSubscriber
     *
     * @throws ModelNotFoundException
     * @throws SubscriptionLimitException
     */
    public function lockAndAssertCanCreateStore(SubscriptionSubscriber $subscriber): SubscriptionSubscriber
    {
        $lockedSubscriber = $subscriber->refreshForUpdate();

        throw_if(method_exists($lockedSubscriber, 'trashed') && $lockedSubscriber->trashed(), (new ModelNotFoundException)->setModel($subscriber::class));

        $this->assertCanCreateStore($lockedSubscriber);

        return $lockedSubscriber;
    }
}
