<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\RestartStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * The restart runs on the storefront queue, the only worker with a runtime socket.
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
