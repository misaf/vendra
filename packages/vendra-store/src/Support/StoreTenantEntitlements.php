<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraStore\Events\StoreLimitApproached;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;
use Misaf\VendraSupport\Tenancy\TenantUsageRegistry;

/**
 * Answer entitlements from the plan of the store's billing reseller.
 *
 * A console-owned store, no store at all, or a reseller that cannot be resolved
 * is unrestricted. The latest plan stands in for a lapsed subscription, so a store
 * in its grace period keeps its limits, and a reseller with no subscription at
 * all is allowed nothing.
 */
final readonly class StoreTenantEntitlements implements TenantEntitlements
{
    /**
     * The shares of a limit, in percent, whose crossing warns the reseller.
     */
    private const array WARNING_PERCENTS = [100, 80];

    public function __construct(
        private StoreResellerResolver $resellerResolver,
        private TenantResolver $tenantResolver,
        private TenantUsageRegistry $usageRegistry,
    ) {}

    public function allows(PlanFeature $feature, ?Model $tenant = null): bool
    {
        $reseller = $this->billedReseller($tenant);

        if ($reseller === null) {
            return true;
        }

        return $this->planOf($reseller)?->allows($feature->value) ?? false;
    }

    public function limit(PlanLimit $limit, ?Model $tenant = null): ?int
    {
        $reseller = $this->billedReseller($tenant);

        if ($reseller === null) {
            return null;
        }

        $plan = $this->planOf($reseller);

        return $plan === null ? 0 : $plan->limit($limit->value);
    }

    public function assertAllows(PlanFeature $feature, ?Model $tenant = null): void
    {
        throw_unless($this->allows($feature, $tenant), EntitlementExceededException::featureUnavailable($feature));
    }

    public function canAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): bool
    {
        $store = $tenant ?? $this->tenantResolver->current();
        $allowed = $this->limit($limit, $store);

        if ($allowed === null || $store === null) {
            return true;
        }

        $usage = $this->usageRegistry->usage($limit, $store) ?? 0;

        return $usage + $amount <= $allowed * $limit->unitSize();
    }

    /**
     * Lock the store row first, so concurrent adds inside transactions count one at a time.
     */
    public function assertCanAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void
    {
        $store = $tenant ?? $this->tenantResolver->current();

        if ($store instanceof Store) {
            Store::query()->withoutGlobalScopes()->whereKey($store->getKey())->lockForUpdate()->value('id');
        }

        throw_unless(
            $this->canAdd($limit, $amount, $tenant),
            EntitlementExceededException::limitReached($limit, $this->limit($limit, $tenant) ?? 0),
        );
    }

    /**
     * Fire one warning per threshold the added amount crossed, highest first.
     */
    public function recordAdded(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void
    {
        $store = $tenant ?? $this->tenantResolver->current();
        $allowed = $this->limit($limit, $store);

        if (! $store instanceof Store || $allowed === null || $allowed === 0) {
            return;
        }

        $capacity = $allowed * $limit->unitSize();
        $usage = $this->usageRegistry->usage($limit, $store) ?? 0;
        $before = $usage - $amount;

        foreach (self::WARNING_PERCENTS as $percent) {
            if ($before * 100 < $capacity * $percent && $usage * 100 >= $capacity * $percent) {
                event(new StoreLimitApproached($store, $limit, $percent));

                return;
            }
        }
    }

    /**
     * @return (Model&SubscriptionSubscriber)|null
     */
    private function billedReseller(?Model $tenant): ?SubscriptionSubscriber
    {
        $store = $tenant ?? $this->tenantResolver->current();

        if (! $store instanceof Store || $store->reseller_id === null) {
            return null;
        }

        return $this->resellerResolver->find($store->reseller_id);
    }

    private function planOf(SubscriptionSubscriber $reseller): ?Plan
    {
        return $reseller->activeSubscription()->plan ?? $reseller->latestSubscription()?->plan;
    }
}
