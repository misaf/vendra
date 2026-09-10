<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraStore\Jobs\CompleteStoreProvisioningJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

final class RetryStoreProvisioningAction
{
    public function execute(Store $store): Store
    {
        return DB::transaction(function () use ($store): Store {
            $lockedStore = Store::query()->whereKey($store->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedStore->provisioning_status === TenantProvisioningStatus::Ready) {
                throw new LogicException("Store [{$lockedStore->id}] is already provisioned.");
            }

            if ($lockedStore->provisioning_status === TenantProvisioningStatus::Failed) {
                $lockedStore->forceFill([
                    'active' => false,
                    'provisioning_status' => TenantProvisioningStatus::Pending,
                    'provisioning_failed_at' => null,
                    'provisioning_error' => null,
                ])->save();
            }

            dispatch(new CompleteStoreProvisioningJob($lockedStore->id))->afterCommit();

            return $lockedStore;
        });
    }
}
