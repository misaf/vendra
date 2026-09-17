<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\RestartStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Restarts an already-deployed storefront.
 *
 * This is not a deployment: nothing is built, pulled, or replaced, so the
 * recorded status and image stay exactly as they were. The intent is recorded
 * here and the restart runs on the storefront queue, the only worker holding a
 * container-runtime socket.
 */
final class RestartStoreStorefrontAction
{
    public function execute(StorefrontDeployment $deployment): void
    {
        $deployment->assertStoreMayServe();

        $deployment->markDesiredState(StorefrontDesiredState::Running);

        dispatch(new RestartStorefrontJob($deployment->id))->afterCommit();
    }
}
