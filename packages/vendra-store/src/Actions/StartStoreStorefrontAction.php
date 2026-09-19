<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Convergence applies the intent on the storefront queue, the only worker with
 * a runtime socket.
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
