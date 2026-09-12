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
 * Move a store to a different billing reseller, or to none.
 *
 * The console owns this operation: a store may be created for one reseller and
 * later handed to another, or taken back and run by the platform directly. A
 * null reseller is that second case, not an error.
 *
 * Reassignment is a creation as far as the receiving reseller's plan is
 * concerned — it consumes a slot they have to have — so it goes through the
 * same {@see StoreQuota} check and the same row lock as a fresh store. Without
 * the lock, two administrators handing two stores to the same reseller could
 * both see its last free slot.
 *
 * This action names no reseller class: the store package sits below the
 * reseller domain, so the reseller arrives typed as a `SubscriptionSubscriber`
 * and only `stores.reseller_id` — a plain nullable key here — records which one
 * it was.
 */
final readonly class AssignStoreResellerAction
{
    public function __construct(private StoreQuota $storeQuota) {}

    /**
     * @param  (Model&SubscriptionSubscriber)|null  $reseller
     */
    public function execute(Store $store, ?SubscriptionSubscriber $reseller): Store
    {
        return DB::transaction(function () use ($store, $reseller): Store {
            $lockedStore = Store::query()
                ->withTrashed()
                ->whereKey($store->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $resellerId = $reseller === null ? null : $this->assertResellerHasRoom($reseller, $lockedStore);

            if ($resellerId === $lockedStore->reseller_id) {
                return $lockedStore;
            }

            $lockedStore->forceFill(['reseller_id' => $resellerId])->save();

            return $lockedStore;
        }, attempts: 5);
    }

    /**
     * The receiving reseller's key, once their plan is known to have room.
     *
     * A store already belonging to this reseller is a no-op rather than a quota
     * failure: re-selecting the current reseller must not fail because they are
     * at their limit, since the store they are "gaining" is one they already
     * have.
     *
     * @param  Model&SubscriptionSubscriber  $reseller
     */
    private function assertResellerHasRoom(SubscriptionSubscriber $reseller, Store $store): mixed
    {
        $lockedReseller = $reseller->newQuery()->lockForUpdate()->whereKey($reseller->getKey())->first();

        if (! $lockedReseller instanceof Model || ! $lockedReseller instanceof SubscriptionSubscriber) {
            throw (new ModelNotFoundException)->setModel($reseller::class);
        }

        if ($lockedReseller->getKey() !== $store->reseller_id) {
            $this->storeQuota->assertCanCreateStore($lockedReseller);
        }

        return $lockedReseller->getKey();
    }
}
