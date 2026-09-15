<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Models\Plan;

final class CreatePlanAction
{
    /**
     * Creates a plan. `PlanObserver` settles the single active default inside
     * the same transaction, so the write and its default hand-off land together.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function execute(array $attributes): Plan
    {
        return DB::transaction(fn (): Plan => Plan::query()->create($attributes));
    }
}
