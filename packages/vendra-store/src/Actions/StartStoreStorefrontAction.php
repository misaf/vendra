<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Starts an already-deployed storefront.
 *
 * This is not a deployment: nothing is built, pulled, or replaced, so the
 * recorded status and image stay exactly as they were. The intent is recorded
 * here and convergence applies it on the storefront queue, the only worker
 * holding a container-runtime socket.
 */
final class StartStoreStorefrontAction
{
    public function execute(StorefrontDeployment $deployment): void
    {
        $deployment->assertStoreMayServe();

        $deployment->markDesiredState(StorefrontDesiredState::Running);

        dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
    }
}
