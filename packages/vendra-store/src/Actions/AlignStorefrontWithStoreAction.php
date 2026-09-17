<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Records the storefront intent a store's status implies and queues convergence.
 *
 * For writers that change a store's status without going through its own
 * lifecycle actions — billing suspension and reactivation above all. A store
 * still provisioning is left alone: its first deployment runs alongside.
 */
final class AlignStorefrontWithStoreAction
{
    public function execute(Store $store): void
    {
        $desiredState = match (true) {
            $store->keepsStorefrontDown() => StorefrontDesiredState::Stopped,
            $store->status() === StoreStatus::Active => StorefrontDesiredState::Running,
            default => null,
        };

        $deployment = StorefrontDeployment::query()->where('store_id', $store->getKey())->first();

        if ($desiredState === null || ! $deployment instanceof StorefrontDeployment || $deployment->desired_state === $desiredState) {
            return;
        }

        $deployment->markDesiredState($desiredState);

        dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
    }
}
