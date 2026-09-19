<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Observers;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Jobs\DestroyStorefrontJob;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Support\StorefrontOrigins;

/**
 * Hooks run inside `$store->execute()` so queries target this store. Every
 * delete path passes through here, so the storefront is never orphaned. Runtime
 * work is queued after commit, so a rolled-back delete stops nothing.
 */
final class StoreObserver
{
    public function deleting(Store $store): void
    {
        $store->execute(function () use ($store): void {
            if ($store->isForceDeleting()) {
                $store->storeDomains()->withTrashed()->forceDelete();

                return;
            }

            $store->storeDomains()->where('active', true)->delete();
        });

        // Bulk query deletes fire no StoreDomain events, so the allowlist is cleared here.
        StorefrontOrigins::forget();

        $this->settleStorefront($store);
    }

    public function restored(Store $store): void
    {
        $store->execute(fn () => $store->storeDomains()->onlyTrashed()->where('active', true)->restore());
        StorefrontOrigins::forget();

        $deployment = $store->storefrontDeployment()->first();

        if ($deployment === null) {
            return;
        }

        /*
         | The inverse of a soft delete: the container was stopped, not removed,
         | so bringing the store back is a start rather than a redeploy. Recording
         | the intent is what makes that happen — convergence reads it and will
         | otherwise keep the storefront down for the same reason it took it down.
         */
        $desiredState = $store->status() === StoreStatus::Active
            ? StorefrontDesiredState::Running
            : StorefrontDesiredState::Stopped;

        $deployment->markDesiredState($desiredState);

        if ($desiredState === StorefrontDesiredState::Stopped) {
            return;
        }

        dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
    }

    /**
     * Stop the storefront on a soft delete, or destroy it on a force delete.
     */
    private function settleStorefront(Store $store): void
    {
        $deployment = $store->storefrontDeployment()->first();

        if ($deployment === null) {
            return;
        }

        if ($store->isForceDeleting()) {
            /*
             | The deployment row is about to disappear with the store's cascade,
             | so the job carries the slug rather than an id it would find nothing
             | behind by the time it runs. That is the whole reason this job is
             | addressed by slug: it has to outlive the record that describes it.
             */
            dispatch(new DestroyStorefrontJob($deployment->slug))->afterCommit();

            return;
        }

        /*
         | Recording the intent is the operation; stopping the container is
         | convergence applying it. Going through reconciliation rather than a
         | dedicated stop job means a lost or failed job is not a storefront left
         | running forever — the next pass reads the same intent and settles it.
         */
        $deployment->markDesiredState(StorefrontDesiredState::Stopped);

        dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
    }
}
