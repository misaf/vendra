<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
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
            $lockedStore = $store->refreshForUpdate();

            throw_if($lockedStore->trashed(), (new ModelNotFoundException)->setModel(Store::class));

            if ($lockedStore->provisioning_status !== TenantProvisioningStatus::Ready) {
                throw new LogicException("Store [{$lockedStore->id}] must finish provisioning before it can be reactivated.");
            }

            $lockedStore->forceFill(['active' => true])->save();

            if ($lockedStore->status() === StoreStatus::Active) {
                $deployment = $lockedStore->storefrontDeployment()->first();

                if ($deployment instanceof StorefrontDeployment) {
                    $deployment->markDesiredState(StorefrontDesiredState::Running);
                    dispatch(new ReconcileStorefrontJob($deployment->id))->afterCommit();
                }
            }

            return $lockedStore;
        });
    }
}
