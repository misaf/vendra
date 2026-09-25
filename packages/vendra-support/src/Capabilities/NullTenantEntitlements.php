<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Enums\PlanLimit;

final class NullTenantEntitlements implements TenantEntitlements
{
    public function allows(PlanFeature $feature, ?Model $tenant = null): bool
    {
        return true;
    }

    public function limit(PlanLimit $limit, ?Model $tenant = null): ?int
    {
        return null;
    }

    public function assertAllows(PlanFeature $feature, ?Model $tenant = null): void {}

    public function canAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): bool
    {
        return true;
    }

    public function assertCanAdd(PlanLimit $limit, int $amount = 1, ?Model $tenant = null): void {}
}
