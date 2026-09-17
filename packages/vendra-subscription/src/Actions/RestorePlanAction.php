<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Models\Plan;

final class RestorePlanAction
{
    /**
     * Restores a soft-deleted plan as a non-default plan: another plan took the
     * default when this one was deleted, and only one plan may hold it.
     */
    public function execute(Plan $plan): Plan
    {
        return DB::transaction(function () use ($plan): Plan {
            $plan->refreshForUpdate();

            if (! $plan->trashed()) {
                return $plan;
            }

            $plan->is_default = false;
            $plan->restore();

            return $plan;
        });
    }
}
