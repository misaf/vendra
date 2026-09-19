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
final class StopStoreStorefrontAction
{
    public function execute(StorefrontDeployment $deployment): void
    {
        $deployment->markDesiredState(StorefrontDesiredState::Stopped);

        dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
    }
}
