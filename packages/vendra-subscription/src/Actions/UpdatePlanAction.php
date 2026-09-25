<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Events\PlanEntitlementsChanged;
use Misaf\VendraSubscription\Models\Plan;

final class UpdatePlanAction
{
    /**
     * `PlanObserver` moves the default in the same transaction. A change to the
     * unit allowance, limits or features raises `PlanEntitlementsChanged`.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function execute(Plan $plan, array $attributes): Plan
    {
        return DB::transaction(function () use ($plan, $attributes): Plan {
            $plan->update($attributes);

            if ($plan->wasChanged(['max_units', 'limits', 'features'])) {
                event(new PlanEntitlementsChanged($plan));
            }

            return $plan;
        });
    }
}
