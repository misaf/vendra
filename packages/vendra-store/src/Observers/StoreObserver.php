<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Observers;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Jobs\DestroyStorefrontJob;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Keeps a store's domains and its storefront in step with the store itself.
 *
 * Every hook runs inside `$store->execute()` so the domain query resolves
 * against this store rather than whatever tenant happens to be active on the
 * request. Synchronous because `deleting` has to see the domains while they are
 * still there, and because `isForceDeleting()` is request state that would not
 * survive a trip through the queue.
 *
 * The storefront cascade lives here rather than in the delete path a panel
 * happens to call, because there is no single such path: the console table, the
 * reseller table, and `OffboardResellerAction` all delete stores directly. A
 * store deleted with its container left running is an orphan still serving the
 * customer's domain, and once a force delete has taken the deployment row with
 * it, nothing is left that knows the container's name. This hook is the one
 * place every delete has to pass through.
 *
 * The runtime work is queued, never done here: only the storefront worker holds
 * a container socket, and the panels run in a container that does not. It is
 * also dispatched `afterCommit` — `OffboardResellerAction` deletes stores inside
 * a transaction, and a rolled-back offboarding must not leave a fleet stopped.
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

        $this->settleStorefront($store);
    }

    public function restored(Store $store): void
    {
        $store->execute(fn () => $store->storeDomains()->onlyTrashed()->where('active', true)->restore());

        $deployment = $this->deploymentFor($store);

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

        ReconcileStorefrontJob::dispatch($deployment->id)->afterCommit();
    }

    /**
     * Bring the storefront in line with what deleting this store means.
     *
     * A soft delete is reversible, so the storefront is stopped and its
     * deployment row kept: the image, the labels and the recorded status all
     * survive, and restoring the store costs a start. A force delete is not
     * reversible, so the container goes.
     */
    private function settleStorefront(Store $store): void
    {
        $deployment = $this->deploymentFor($store);

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
            DestroyStorefrontJob::dispatch($deployment->slug)->afterCommit();

            return;
        }

        /*
         | Recording the intent is the operation; stopping the container is
         | convergence applying it. Going through reconciliation rather than a
         | dedicated stop job means a lost or failed job is not a storefront left
         | running forever — the next pass reads the same intent and settles it.
         */
        $deployment->markDesiredState(StorefrontDesiredState::Stopped);

        ReconcileStorefrontJob::dispatch($deployment->id)->afterCommit();
    }

    /**
     * A store owns at most one storefront, and this reads it from outside any
     * store's context — the caller may be deleting a store that is not current.
     */
    private function deploymentFor(Store $store): ?StorefrontDeployment
    {
        return StorefrontDeployment::query()
            ->where('store_id', $store->getKey())
            ->first();
    }
}
