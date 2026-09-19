<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;

/**
 * Used when a store's status changes outside its own lifecycle actions, such as
 * billing suspension. A store that is still provisioning is left alone.
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

        $deployment = $store->storefrontDeployment()->first();

        if ($desiredState === null || ! $deployment instanceof StorefrontDeployment || $deployment->desired_state === $desiredState) {
            return;
        }

        $deployment->markDesiredState($desiredState);

        dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
    }
}
