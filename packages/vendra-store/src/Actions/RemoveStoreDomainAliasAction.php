<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontRuntimeConfiguration;

final readonly class RemoveStoreDomainAliasAction
{
    public function __construct(private StorefrontRuntimeConfiguration $runtime) {}

    /**
     * The primary domain is the one the store was created with and is never
     * removed. The alias is kept as trashed history. The storefront is redeployed
     * because its routing label cannot change in place.
     */
    public function execute(Store $store, StoreDomain $domain): void
    {
        throw_if($store->trashed(), (new ModelNotFoundException)->setModel(Store::class));

        throw_if(
            $domain->store_id !== $store->getKey() || ! $domain->active || $domain->is_primary || $domain->trashed(),
            InvalidArgumentException::class,
            'Only an active alias domain of this store can be removed.',
        );

        $store->execute(fn () => DB::transaction(function () use ($domain): void {
            $domain->forceFill(['active' => false])->save();
            $domain->delete();
        }));

        $deployment = $store->storefrontDeployment()->first();

        if ($deployment instanceof StorefrontDeployment && $this->runtime->isConfigured()) {
            dispatch(new ProvisionStorefrontJob($deployment->id, force: true))->afterCommit();
        }
    }
}
