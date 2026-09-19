<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Support\StoreQuota;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;

/**
 * A null reseller means the platform runs the store directly. Reassignment
 * consumes a slot in the receiving reseller's plan, so it takes the same
 * {@see StoreQuota} check and row lock as a new store.
 */
final readonly class AssignStoreResellerAction
{
    public function __construct(
        private StoreQuota $storeQuota,
        private AlignStorefrontWithStoreAction $alignStorefront,
    ) {}

    /**
     * @param  (Model&SubscriptionSubscriber)|null  $reseller
     */
    public function execute(Store $store, ?SubscriptionSubscriber $reseller): Store
    {
        return DB::transaction(function () use ($store, $reseller): Store {
            $lockedStore = $store->refreshForUpdate();

            throw_if($lockedStore->trashed(), (new ModelNotFoundException)->setModel(Store::class));

            $resellerId = $reseller === null ? null : $this->assertResellerHasRoom($reseller, $lockedStore);

            if ($resellerId === $lockedStore->reseller_id) {
                return $lockedStore;
            }

            /*
             | A billing suspension belonged to the previous reseller's lapsed
             | plan. The receiving reseller passed the quota check, and a store
             | the console takes back is not billed at all.
             */
            $lockedStore->forceFill([
                'reseller_id' => $resellerId,
                'billing_suspended_at' => null,
            ])->save();

            $this->alignStorefront->execute($lockedStore);

            return $lockedStore;
        }, attempts: 5);
    }

    /**
     * Get the receiving reseller's key once their plan is known to have room.
     *
     * Re-selecting the store's current reseller never fails the quota check.
     *
     * @param  Model&SubscriptionSubscriber  $reseller
     */
    private function assertResellerHasRoom(SubscriptionSubscriber $reseller, Store $store): mixed
    {
        if ($reseller->getKey() === $store->reseller_id) {
            return $store->reseller_id;
        }

        return $this->storeQuota->lockAndAssertCanCreateStore($reseller)->getKey();
    }
}
