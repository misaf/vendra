<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraStore\Contracts\StoreResellerResolver;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Support\StoreQuota;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

final readonly class RestoreOffboardedStoreAction
{
    public function __construct(
        private StoreResellerResolver $resellerResolver,
        private StoreQuota $storeQuota,
    ) {}

    public function execute(Store $store): Store
    {
        return DB::transaction(function () use ($store): Store {
            $lockedStore = Store::query()
                ->withTrashed()
                ->whereKey($store->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedStore->trashed()) {
                return $lockedStore;
            }

            $this->assertResellerHasRoom($lockedStore);

            $metadata = $lockedStore->metadata ?? [];
            Arr::set($metadata, 'offboarding.restored_at', now()->toIso8601String());

            $shouldReactivate = $lockedStore->provisioning_status === TenantProvisioningStatus::Ready
                && Arr::get($metadata, 'offboarding.previous_active', false) === true;

            $lockedStore->forceFill([
                'active' => $shouldReactivate,
                'metadata' => $metadata,
            ])->save();
            $lockedStore->restore();

            return $lockedStore;
        }, attempts: 5);
    }

    private function assertResellerHasRoom(Store $store): void
    {
        if ($store->reseller_id === null) {
            return;
        }

        $reseller = $this->resellerResolver->find($store->reseller_id);

        if (! $reseller instanceof Model || ! $reseller instanceof SubscriptionSubscriber) {
            throw new LogicException("Store [{$store->id}] cannot be restored because its billing reseller is unavailable.");
        }

        $lockedReseller = $reseller->newQuery()->whereKey($reseller->getKey())->lockForUpdate()->firstOrFail();

        if (! $lockedReseller instanceof SubscriptionSubscriber) {
            throw new LogicException("Store [{$store->id}] has an invalid billing reseller.");
        }

        $this->storeQuota->assertCanCreateStore($lockedReseller);
    }
}
