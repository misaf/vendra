<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Observers\StoreObserver;

/**
 * The storefront cascade lives in {@see StoreObserver} so that every delete
 * path stops or destroys the storefront, not just this action.
 */
final class DeleteStoreAction
{
    public function execute(Store $store, bool $force = false): void
    {
        DB::transaction(function () use ($store, $force): void {
            $force ? $store->forceDelete() : $store->delete();
        });
    }
}
