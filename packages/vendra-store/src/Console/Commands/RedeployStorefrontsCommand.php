<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Database\Eloquent\Builder;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Rebuilds every storefront the platform intends to be running.
 *
 * Destructive by design, which is why it is its own command rather than a flag
 * on `storefront:reconcile`. Provisioning replaces a container instead of
 * updating it, so each storefront here is removed and recreated and is down for
 * its own pull, start, and health gate — sequentially, since one worker serves
 * the storefront queue. On an estate of any size that is an outage, and it
 * should be asked for by name.
 *
 * Reach for it when the change is one convergence cannot see: a storefront image
 * republished under the same reference, or an edge label that only a fresh
 * container will carry. Ordinary drift is `storefront:reconcile`.
 */
#[Description('Rebuild every storefront intended to be running, whatever its recorded status')]
#[Signature('storefront:redeploy
        {--sync : Redeploy each storefront in the current process}
        {--force-unique : Drop a stale unique lock left by a killed worker; skips the guard against provisioning one storefront twice}')]
final class RedeployStorefrontsCommand extends StorefrontDeploymentDispatchCommand
{
    /**
     * Only storefronts meant to be up.
     *
     * A storefront somebody deliberately stopped is not rebuilt: doing so would
     * start it again, which is the one thing `desired_state` exists to prevent.
     *
     * @return Builder<StorefrontDeployment>
     */
    protected function query(): Builder
    {
        return StorefrontDeployment::query()->desiredRunning();
    }

    /**
     * Forced: rebuilding is the entire request, so a Ready status must not
     * short-circuit it.
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
