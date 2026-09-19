<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Models\Store;

final readonly class SuspendStoreForBillingAction
{
    public function __construct(private AlignStorefrontWithStoreAction $alignStorefrontWithStoreAction) {}

    public function execute(Store $store): Store
    {
        return DB::transaction(function () use ($store): Store {
            $lockedStore = $store->refreshForUpdate();

            $lockedStore->forceFill(['billing_suspended_at' => now()])->save();

            $this->alignStorefrontWithStoreAction->execute($lockedStore);

            return $lockedStore;
        });
    }
}
