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

final readonly class MakeStoreDomainPrimaryAction
{
    public function __construct(private StorefrontRuntimeConfiguration $runtime) {}

    /**
     * Promote one of the store's alias domains; the previous primary stays active as an alias.
     *
     * The current primary is demoted first, because the database allows only
     * one live primary per store. The storefront is redeployed on its new
     * canonical host.
     */
    public function execute(Store $store, StoreDomain $domain): StoreDomain
    {
        throw_if($store->trashed(), (new ModelNotFoundException)->setModel(Store::class));

        throw_if(
            $domain->store_id !== $store->getKey() || ! $domain->active || $domain->trashed(),
            InvalidArgumentException::class,
            'Only an active domain of this store can become its primary domain.',
        );

        if ($domain->is_primary) {
            return $domain;
        }

        $deployment = $store->storefrontDeployment()->first();

        $store->execute(fn () => DB::transaction(function () use ($store, $domain, $deployment): void {
            $store->storeDomains()
                ->primary()
                ->get()
                ->each(fn (StoreDomain $current): bool => $current->forceFill(['is_primary' => false])->save());

            $domain->forceFill(['is_primary' => true])->save();

            $deployment?->forceFill(['domain' => $domain->name])->save();
        }));

        if ($deployment instanceof StorefrontDeployment && $this->runtime->isConfigured()) {
            dispatch(new ProvisionStorefrontJob($deployment->id, force: true))->afterCommit();
        }

        return $domain;
    }
}
