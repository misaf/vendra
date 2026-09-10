<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;

final class SuspendStoreAction
{
    public function execute(Store $store): Store
    {
        return DB::transaction(function () use ($store): Store {
            $lockedStore = Store::query()->whereKey($store->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedStore->active) {
                $lockedStore->forceFill(['active' => false])->save();
            }

            $deployment = $this->deploymentFor($lockedStore);

            if ($deployment instanceof StorefrontDeployment) {
                $deployment->markDesiredState(StorefrontDesiredState::Stopped);
                dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
            }

            return $lockedStore;
        });
    }

    private function deploymentFor(Store $store): ?StorefrontDeployment
    {
        return StorefrontDeployment::query()->where('store_id', $store->getKey())->first();
    }
}
