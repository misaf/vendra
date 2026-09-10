<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;

final class StoreQuota
{
    /**
     * Whether the subscriber may create another store right now.
     *
     * @param  Model&SubscriptionSubscriber  $subscriber
     */
    public function canCreateStore(SubscriptionSubscriber $subscriber): bool
    {
        if (! $subscriber->isSubscriptionActive()) {
            return false;
        }

        $plan = $subscriber->activeSubscription()?->plan;

        if ($plan === null) {
            return false;
        }

        return $subscriber->subscribedUnitCount() < $plan->max_units;
    }

    /**
     * The number of additional stores the subscriber may still create.
     *
     * @param  Model&SubscriptionSubscriber  $subscriber
     */
    public function remainingStores(SubscriptionSubscriber $subscriber): int
    {
        if (! $subscriber->isSubscriptionActive()) {
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
     * @throws SubscriptionLimitException when the subscriber may not create a store
     */
    public function assertCanCreateStore(SubscriptionSubscriber $subscriber): void
    {
        if (! $subscriber->isSubscriptionActive()) {
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
}
