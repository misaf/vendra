<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanLimit;

/**
 * Find the plan limits a tenant already uses more of than its plan allows.
 *
 * Adds are refused at the limit, so a tenant only ends up over one when its
 * plan changes or the plan's limits are lowered.
 */
final readonly class TenantLimitOverages
{
    public function __construct(
        private TenantEntitlements $entitlements,
        private TenantUsageRegistry $usageRegistry,
    ) {}

    /**
     * @return list<PlanLimit>
     */
    public function exceeded(Model $tenant): array
    {
        $exceeded = [];

        foreach (PlanLimit::cases() as $limit) {
            $allowed = $this->entitlements->limit($limit, $tenant);
            $usage = $this->usageRegistry->usage($limit, $tenant);

            if ($allowed !== null && $usage !== null && $usage > $allowed * $limit->unitSize()) {
                $exceeded[] = $limit;
            }
        }

        return $exceeded;
    }
}
