<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

final readonly class ReactivateStoreAction
{
    public function __construct(private AlignStorefrontWithStoreAction $alignStorefrontWithStoreAction) {}

    public function execute(Store $store): Store
    {
        return DB::transaction(function () use ($store): Store {
            $lockedStore = $store->refreshForUpdate();

            throw_if($lockedStore->trashed(), (new ModelNotFoundException)->setModel(Store::class));

            if ($lockedStore->provisioning_status !== TenantProvisioningStatus::Ready) {
                throw new LogicException("Store [{$lockedStore->id}] must finish provisioning before it can be reactivated.");
            }

            $lockedStore->forceFill(['active' => true])->save();

            $this->alignStorefrontWithStoreAction->execute($lockedStore);

            return $lockedStore;
        });
    }
}
