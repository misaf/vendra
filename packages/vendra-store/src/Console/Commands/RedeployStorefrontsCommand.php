<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Every storefront goes down while its container is replaced, so this is a
 * separate command. Use it for changes convergence cannot see, such as an image
 * republished under the same tag; ordinary drift is `vendra-store:reconcile`.
 */
#[Description('Rebuild every storefront intended to be running, whatever its recorded status')]
#[Signature('vendra-store:redeploy
        {--sync : Redeploy each storefront in the current process}
        {--force-unique : Drop a stale unique lock left by a killed worker; skips the guard against provisioning one storefront twice}')]
final class RedeployStorefrontsCommand extends StorefrontDeploymentDispatchCommand
{
    /**
     * Select only storefronts meant to be running, so stopped ones stay stopped.
     *
     * @return Builder<StorefrontDeployment>
     */
    protected function query(): Builder
    {
        return StorefrontDeployment::query()->desiredRunning();
    }

    /**
     * Force the deploy so a Ready status does not skip the rebuild.
     */
    protected function jobFor(int $deploymentId): object
    {
        return new ProvisionStorefrontJob($deploymentId, force: true);
    }

    protected function summary(): string
    {
        return '%d storefront deployment(s) %s.';
    }

    protected function syncVerb(): string
    {
        return 'redeployed';
    }

    protected function queuedVerb(): string
    {
        return 'queued for redeployment';
    }
}
