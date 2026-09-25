<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Support\SubscriptionRegistry;
use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

final readonly class StoreQuota
{
    public function __construct(private SubscriptionRegistry $subscriptionRegistry) {}

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
     * Assert the subscriber's plan covers a store on the given domain.
     *
     * @param  Model&SubscriptionSubscriber  $subscriber
     *
     * @throws EntitlementExceededException
     */
    public function assertCanUseDomain(SubscriptionSubscriber $subscriber, string $domain): void
    {
        if (! StoreDomain::isCustom($domain)) {
            return;
        }

        $plan = $subscriber->activeSubscription()?->plan;

        throw_unless(
            $plan?->allows(PlanFeature::CustomDomain->value) ?? false,
            EntitlementExceededException::featureUnavailable(PlanFeature::CustomDomain),
        );
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
        $lockedSubscriber = $this->subscriptionRegistry->lockSubscriber($subscriber);

        $this->assertCanCreateStore($lockedSubscriber);

        return $lockedSubscriber;
    }
}
