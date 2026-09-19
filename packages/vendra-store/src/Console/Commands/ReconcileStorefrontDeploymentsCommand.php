<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraStore\Actions\ReconcileStoreStorefrontAction;
use Misaf\VendraStore\Enums\StorefrontReconciliationOutcome;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Safe to repeat: each storefront gets the narrowest fix, so a converged estate
 * is left untouched. Use `vendra-store:redeploy` to rebuild deliberately.
 */
#[Description('Converge every storefront runtime with the state the database intends')]
#[Signature('vendra-store:reconcile
        {--sync : Reconcile each storefront in the current process}
        {--force-unique : Drop a stale unique lock left by a killed worker; skips the guard against provisioning one storefront twice}')]
final class ReconcileStorefrontDeploymentsCommand extends StorefrontDeploymentDispatchCommand
{
    /**
     * @return Builder<StorefrontDeployment>
     */
    protected function query(): Builder
    {
        return StorefrontDeployment::query();
    }

    protected function jobFor(int $deploymentId): object
    {
        return new ReconcileStorefrontJob($deploymentId);
    }

    /**
     * Converge directly, since a synchronously dispatched job does not return
     * the handler's outcome.
     */
    protected function performSync(int $deploymentId): mixed
    {
        $deployment = StorefrontDeployment::query()->find($deploymentId);

        return $deployment instanceof StorefrontDeployment
            ? $this->laravel->make(ReconcileStoreStorefrontAction::class)->execute($deployment)
            : null;
    }

    protected function summary(): string
    {
        return '%d storefront deployment(s) %s.';
    }

    protected function syncVerb(): string
    {
        return 'reconciled';
    }

    protected function queuedVerb(): string
    {
        return 'queued for reconciliation';
    }

    /**
     * @param  list<mixed>  $outcomes
     */
    protected function reportOutcomes(array $outcomes): void
    {
        $tally = [];

        foreach ($outcomes as $outcome) {
            if ($outcome instanceof StorefrontReconciliationOutcome) {
                $tally[$outcome->value] = ($tally[$outcome->value] ?? 0) + 1;
            }
        }

        if ($tally === []) {
            return;
        }

        ksort($tally);

        foreach ($tally as $label => $count) {
            $this->line(sprintf('  %s: %d', $label, $count));
        }
    }
}
