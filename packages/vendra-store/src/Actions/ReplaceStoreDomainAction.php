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
use UnexpectedValueException;

final readonly class ReplaceStoreDomainAction
{
    public function __construct(private StorefrontRuntimeConfiguration $runtime) {}

    /**
     * The previous domain is kept as trashed history. Demotion and creation
     * share a transaction so a failed create never leaves the store without a
     * domain. The storefront is redeployed because its routing label cannot
     * change in place. The caller validates the domain first.
     */
    public function execute(Store $store, string $domain): StoreDomain
    {
        /*
         | An offboarded store's trashed active domain is invisible here, so a
         | replace would leave two active domains once the store is restored.
         */
        throw_if($store->trashed(), (new ModelNotFoundException)->setModel(Store::class));

        /*
         | Read before the transaction, written inside it. A store owns at most
         | one storefront, and this is the model the write below and the dispatch
         | afterwards both act on — held as an object rather than reassigned out
         | of the closure, because the arrow function wrapping the transaction
         | captures by value and a reference through it would not survive.
         */
        $deployment = $store->storefrontDeployment()->first();

        $storeDomain = $store->execute(fn (): StoreDomain => DB::transaction(function () use ($store, $domain, $deployment): StoreDomain {
            $store->storeDomains()
                ->where('active', true)
                ->get()
                ->each(function (StoreDomain $current): void {
                    $current->forceFill(['active' => false])->save();
                    $current->delete();
                });

            $created = $store->storeDomains()->create([
                'name' => $domain,
                'active' => true,
            ]);

            /*
             | Inside the transaction: the deployment's domain and the store's
             | active domain describe the same fact, and a replace that applied
             | one without the other is exactly the state convergence would go on
             | reading as correct.
             */
            $deployment?->forceFill(['domain' => $domain])->save();

            return $created;
        }));

        throw_unless($storeDomain instanceof StoreDomain, UnexpectedValueException::class, 'Replacing a store domain did not return a domain model.');

        /*
         | Forced, because the deployment is already recorded as Ready and an
         | unforced deploy would return without doing anything — which is the one
         | case where the recorded status is exactly what must not be trusted.
         |
         | Skipped with no runtime configured, as everywhere else that dispatches
         | provisioning: the row now carries the new domain, and a converge pass
         | rebuilds the storefront on it once the estate is up.
         */
        if ($deployment instanceof StorefrontDeployment && $this->runtime->isConfigured()) {
            dispatch(new ProvisionStorefrontJob($deployment->id, force: true))->afterCommit();
        }

        return $storeDomain;
    }
}
