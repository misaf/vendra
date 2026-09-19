<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Panels have no runtime socket, so {@see ReconcileStoreStorefrontAction} runs
 * on the storefront queue.
 */
final class RequestStorefrontReconciliationAction
{
    public function execute(StorefrontDeployment $deployment): void
    {
        dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
    }
}
