<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Observers;

use Misaf\VendraSubscription\Exceptions\PlanInUseException;
use Misaf\VendraSubscription\Models\Plan;

final class PlanObserver
{
    /**
     * A plan that still backs a subscription cannot be removed. The throw has
     * to abort the delete, so this observer stays synchronous.
     */
    public function deleting(Plan $plan): void
    {
        if ($plan->isInUse()) {
            throw PlanInUseException::forPlan($plan);
        }
    }

    public function creating(Plan $plan): void
    {
        if (! $plan->active) {
            $plan->is_default = false;

            return;
        }

        if (! Plan::query()->active()->exists()) {
            $plan->is_default = true;
        }
    }

    public function saving(Plan $plan): void
    {
        if (! $plan->active) {
            $plan->is_default = false;

            return;
        }

        if ($plan->is_default) {
            Plan::query()
                ->where('is_default', true)
                ->whereKeyNot($plan->getKey())
                ->update(['is_default' => false]);

            return;
        }

        if ($plan->exists && $plan->getOriginal('is_default') === true) {
            $hasAnotherDefault = Plan::query()
                ->active()
                ->where('is_default', true)
                ->whereKeyNot($plan->getKey())
                ->exists();

            if (! $hasAnotherDefault) {
                $plan->is_default = true;
            }
        }
    }

    public function saved(Plan $plan): void
    {
        if ($plan->wasChanged(['active', 'is_default'])) {
            $this->ensureActiveDefault();
        }
    }

    public function deleted(Plan $plan): void
    {
        if (! $plan->is_default) {
            return;
        }

        $this->ensureActiveDefault();
    }

    private function ensureActiveDefault(): void
    {
        if (Plan::query()->active()->where('is_default', true)->exists()) {
            return;
        }

        Plan::query()
            ->active()
            ->orderBy('id')
            ->first()
            ?->update(['is_default' => true]);
    }
}
