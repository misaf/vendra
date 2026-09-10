<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

#[Description('Retry storefront deployments currently marked as failed')]
#[Signature('storefront:retry-failed
        {--sync : Retry each failed storefront in the current process}
        {--force-unique : Drop a stale unique lock left by a killed worker; skips the guard against provisioning one storefront twice}')]
final class RetryFailedStorefrontDeploymentsCommand extends StorefrontDeploymentDispatchCommand
{
    /**
     * @return Builder<StorefrontDeployment>
     */
    protected function query(): Builder
    {
        return StorefrontDeployment::query()->where('status', StorefrontDeploymentStatus::Failed->value);
    }

    /**
     * Unforced: these rows are Failed, so nothing short-circuits, and a status
     * that changed since selection is worth respecting.
     */
    protected function jobFor(int $deploymentId): object
    {
        return new ProvisionStorefrontJob($deploymentId);
    }

    protected function summary(): string
    {
        return '%d failed storefront deployment(s) %s.';
    }

    protected function syncVerb(): string
    {
        return 'retried';
    }

    protected function queuedVerb(): string
    {
        return 'queued for retry';
    }
}
