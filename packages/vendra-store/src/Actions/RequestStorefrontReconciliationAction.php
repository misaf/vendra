<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Asks the storefront worker to converge one storefront.
 *
 * Panels run in a container without a runtime socket, so they cannot observe or
 * correct a container themselves; {@see ReconcileStoreStorefrontAction} does that
 * work on the storefront queue.
 */
final class RequestStorefrontReconciliationAction
{
    public function execute(StorefrontDeployment $deployment): void
    {
        dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
    }
}
