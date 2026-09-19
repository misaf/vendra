<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Models\Plan;

final class UpdatePlanAction
{
    /**
     * `PlanObserver` moves the default in the same transaction.
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
