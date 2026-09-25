<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Support;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraStore\Contracts\StoreResellerResolver;
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

    public function assertCanAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void
    {
        $store = $tenant ?? $this->tenantResolver->current();
        $allowed = $this->limit($limit, $store);

        if ($allowed === null || $store === null) {
            return;
        }

        $usage = $this->usageRegistry->usage($limit, $store) ?? 0;

        throw_if($usage + $amount > $allowed * $limit->unitSize(), EntitlementExceededException::limitReached($limit, $allowed));
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
