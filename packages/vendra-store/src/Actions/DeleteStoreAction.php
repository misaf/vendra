<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Observers\StoreObserver;

/**
 * Deletes a store, with its storefront settled by the same delete.
 *
 * The storefront half is not here. It used to be, and that was the bug: a store
 * deleted through any other path — the console table, the reseller table,
 * `OffboardResellerAction` — left its container running and serving the
 * customer's domain. The cascade now lives in {@see StoreObserver}, which every
 * delete passes through whether it came from an action or not, so a soft delete
 * stops the storefront and a force delete destroys it no matter who asked.
 *
 * What survives here is the transaction: deleting a store writes to `stores`,
 * `store_domains`, and `storefront_deployments`, and a half-applied delete is a
 * store with no domains still answering as though it had them.
 */
final class DeleteStoreAction
{
    /**
     * @param  bool  $force  delete the store permanently rather than soft-deleting it
     */
    public function execute(Store $store, bool $force = false): void
    {
        DB::transaction(function () use ($store, $force): void {
            $force ? $store->forceDelete() : $store->delete();
        });
    }
}
