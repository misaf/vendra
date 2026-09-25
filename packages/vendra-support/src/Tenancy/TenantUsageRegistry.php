<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Enums\PlanLimit;

/**
 * Packages register how much of a plan limit a tenant uses.
 *
 * Counters query by the given tenant's key rather than the current tenant, so
 * a caller can count every store of a reseller.
 */
final class TenantUsageRegistry
{
    /** @var array<string, Closure(Model): int> */
    private array $counters = [];

    /**
     * @param  Closure(Model): int  $counter
     */
    public function register(PlanLimit $limit, Closure $counter): void
    {
        $this->counters[$limit->value] = $counter;
    }

    public function usage(PlanLimit $limit, Model $tenant): ?int
    {
        $counter = $this->counters[$limit->value] ?? null;

        return $counter === null ? null : $counter($tenant);
    }
}
