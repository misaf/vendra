<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Exceptions\PlanInUseException;
use Misaf\VendraSubscription\Models\Plan;

final class DeletePlanAction
{
    /**
     * Soft-deletes a plan no subscription references.
     *
     * @throws PlanInUseException when a subscription, even a trashed one, still references the plan
     */
    public function execute(Plan $plan): void
    {
        DB::transaction(function () use ($plan): void {
            Plan::query()->whereKey($plan->getKey())->lockForUpdate()->firstOrFail()->delete();
        });
    }
}
