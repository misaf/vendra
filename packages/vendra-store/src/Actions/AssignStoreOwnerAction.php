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
 * Move a store to a different billing owner, or to none.
 *
 * The console owns this operation: a store may be created for one reseller and
 * later handed to another, or taken back and run by the platform directly. A
 * null owner is that second case, not an error.
 *
 * Reassignment is a creation as far as the receiving owner's plan is concerned
 * — it consumes a slot they have to have — so it goes through the same
 * {@see StoreQuota} check and the same row lock as a fresh store. Without the
 * lock, two operators handing two stores to the same reseller could both see
 * its last free slot.
 *
 * This action names no reseller: the store package sits below the reseller
 * domain, so an owner arrives typed as a `SubscriptionSubscriber` and only
 * `stores.reseller_id` — a plain nullable key here — records which one it was.
 */
final readonly class AssignStoreOwnerAction
{
    public function __construct(private StoreQuota $storeQuota) {}

    /**
     * @param  (Model&SubscriptionSubscriber)|null  $owner
     */
    public function execute(Store $store, ?SubscriptionSubscriber $owner): Store
    {
        return DB::transaction(function () use ($store, $owner): Store {
            $lockedStore = Store::query()
                ->withTrashed()
                ->whereKey($store->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $ownerId = $owner === null ? null : $this->assertOwnerHasRoom($owner, $lockedStore);

            if ($ownerId === $lockedStore->reseller_id) {
                return $lockedStore;
            }

            $lockedStore->forceFill(['reseller_id' => $ownerId])->save();

            return $lockedStore;
        }, attempts: 5);
    }

    /**
     * The receiving owner's key, once their plan is known to have room.
     *
     * A store already belonging to this owner is a no-op rather than a quota
     * failure: re-selecting the current owner must not fail because they are at
     * their limit, since the store they are "gaining" is one they already have.
     *
     * @param  Model&SubscriptionSubscriber  $owner
     */
    private function assertOwnerHasRoom(SubscriptionSubscriber $owner, Store $store): mixed
    {
        $lockedOwner = $owner->newQuery()->lockForUpdate()->whereKey($owner->getKey())->first();

        if (! $lockedOwner instanceof Model || ! $lockedOwner instanceof SubscriptionSubscriber) {
            throw (new ModelNotFoundException)->setModel($owner::class);
        }

        if ($lockedOwner->getKey() !== $store->reseller_id) {
            $this->storeQuota->assertCanCreateStore($lockedOwner);
        }

        return $lockedOwner->getKey();
    }
}
