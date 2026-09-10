<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

final class ReactivateStoreAction
{
    public function execute(Store $store): Store
    {
        return DB::transaction(function () use ($store): Store {
            $lockedStore = Store::query()->whereKey($store->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedStore->provisioning_status !== TenantProvisioningStatus::Ready) {
                throw new LogicException("Store [{$lockedStore->id}] must finish provisioning before it can be reactivated.");
            }

            $lockedStore->forceFill(['active' => true])->save();

            if ($lockedStore->status() === StoreStatus::Active) {
                $deployment = StorefrontDeployment::query()
                    ->where('store_id', $lockedStore->getKey())
                    ->first();

                if ($deployment instanceof StorefrontDeployment) {
                    $deployment->markDesiredState(StorefrontDesiredState::Running);
                    dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
                }
            }

            return $lockedStore;
        });
    }
}
