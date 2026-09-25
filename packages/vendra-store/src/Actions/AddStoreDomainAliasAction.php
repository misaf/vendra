<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontRuntimeConfiguration;
use Misaf\VendraSupport\Contracts\TenantEntitlements;
use Misaf\VendraSupport\Enums\PlanFeature;
use Misaf\VendraSupport\Enums\PlanLimit;
use Misaf\VendraSupport\Exceptions\EntitlementExceededException;
use UnexpectedValueException;

final readonly class AddStoreDomainAliasAction
{
    public function __construct(
        private StorefrontRuntimeConfiguration $runtime,
        private TenantEntitlements $entitlements,
    ) {}

    /**
     * The storefront is redeployed because its routing label cannot change in
     * place. The caller validates the domain first.
     *
     * The store row is locked so two aliases cannot both take the last slot.
     *
     * @throws EntitlementExceededException
     */
    public function execute(Store $store, string $domain): StoreDomain
    {
        throw_if($store->trashed(), (new ModelNotFoundException)->setModel(Store::class));

        $storeDomain = $store->execute(fn (): StoreDomain => DB::transaction(function () use ($store, $domain): StoreDomain {
            Store::query()->whereKey($store->getKey())->lockForUpdate()->first();

            if (StoreDomain::isCustom($domain)) {
                $this->entitlements->assertAllows(PlanFeature::CustomDomain, $store);
            }

            $this->entitlements->assertCanAdd(PlanLimit::DomainsPerStore, tenant: $store);

            return $store->storeDomains()->create([
                'name' => $domain,
                'active' => true,
                'is_primary' => false,
            ]);
        }));

        throw_unless($storeDomain instanceof StoreDomain, UnexpectedValueException::class, 'Adding a store domain alias did not return a domain model.');

        $deployment = $store->storefrontDeployment()->first();

        if ($deployment instanceof StorefrontDeployment && $this->runtime->isConfigured()) {
            dispatch(new ProvisionStorefrontJob($deployment->id, force: true))->afterCommit();
        }

        return $storeDomain;
    }
}
