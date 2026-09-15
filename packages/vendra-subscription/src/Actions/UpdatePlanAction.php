<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Models\Plan;

final class UpdatePlanAction
{
    /**
     * Updates a plan. Deactivating or un-defaulting it makes `PlanObserver`
     * move the default to another active plan, so both writes share a transaction.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Plan $plan, array $attributes): Plan
    {
        return DB::transaction(function () use ($plan, $attributes): Plan {
            $plan->update($attributes);

            return $plan;
        });
    }
}
