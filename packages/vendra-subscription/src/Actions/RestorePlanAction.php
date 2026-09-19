<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraSubscription\Models\Plan;

final class RestorePlanAction
{
    /**
     * The plan comes back as non-default, since another plan took the default.
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
