<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Str;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;
use Throwable;

/**
 * A store that finished provisioning is never marked failed, so a late failure
 * from an overlapping attempt cannot take it offline.
 */
final class FailStoreProvisioningAction
{
    public function execute(int $storeId, ?Throwable $exception): void
    {
        Store::query()
            ->whereKey($storeId)
            ->where('provisioning_status', '!=', TenantProvisioningStatus::Ready->value)
            ->update([
                'active' => false,
                'provisioning_status' => TenantProvisioningStatus::Failed->value,
                'provisioning_failed_at' => now(),
                'provisioning_error' => $exception === null
                    ? 'Store provisioning failed.'
                    : Str::limit($exception->getMessage(), 2000, ''),
            ]);
    }
}
