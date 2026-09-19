<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Models\Plan;

final class CreatePlanAction
{
    /**
     * `PlanObserver` settles the default in the same transaction.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Plan
    {
        return DB::transaction(fn (): Plan => Plan::query()->create($attributes));
    }
}
