<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Exceptions\PlanInUseException;
use Misaf\VendraSubscription\Models\Plan;

final class DeletePlanAction
{
    /**
     * A plan that any subscription references, even a trashed one, is refused.
     *
     * @throws PlanInUseException
     */
    public function execute(Plan $plan): void
    {
        DB::transaction(function () use ($plan): void {
            $plan->refreshForUpdate();

            throw_if($plan->trashed(), (new ModelNotFoundException)->setModel(Plan::class));

            $plan->delete();
        });
    }
}
