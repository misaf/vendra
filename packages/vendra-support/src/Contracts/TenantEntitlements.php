<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Contracts;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;

/**
 * Answers what the plan behind a tenant allows. A null tenant means the current one.
 */
interface TenantEntitlements
{
    public function allows(PlanFeature $feature, ?Model $tenant = null): bool;

    /**
     * Get the limit in the limit's own unit, or null when it is unlimited.
     */
    public function limit(PlanLimit $limit, ?Model $tenant = null): ?int;

    /**
     * @throws EntitlementExceededException
     */
    public function assertAllows(PlanFeature $feature, ?Model $tenant = null): void;

    /**
     * Determine whether the tenant may add the given amount, counted like its usage counter.
     */
    public function canAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): bool;

    /**
     * Assert the tenant may add the given amount, counted like its usage counter.
     *
     * Called inside a transaction, it holds a lock on the tenant until commit, so
     * concurrent adds cannot both take the last slot.
     *
     * @throws EntitlementExceededException
     */
    public function assertCanAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void;

    /**
     * Report an amount that was just added, so the tenant's reseller can be warned
     * when it pushes usage toward the limit.
     */
    public function recordAdded(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void;
}
