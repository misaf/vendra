<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Actions;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
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
     * Replace a store's active domain, retaining the previous one as history.
     *
     * The current active domain (active = true) is demoted to a replaced
     * history record (active = false) and soft-deleted, so it stops resolving
     * but stays visible behind the trashed filter. A fresh active domain is
     * then created. Runs in the store's own tenant context so the domain
     * records are scoped to this store regardless of the currently active one.
     *
     * Demotion and creation share one transaction. Without it a failing
     * create — a unique collision on the new domain is the likely one — leaves
     * the previous domain already deactivated and trashed, so the store
     * resolves to nothing and is unreachable with no automatic way back.
     *
     * The storefront moves with it. A storefront is routed by a `Host()` rule in
     * a container label, and a container's labels cannot be edited in place, so
     * following the store to its new domain means replacing the container —
     * which is what provisioning does anyway. Leaving that out was the bug: the
     * storefront went on answering the old domain, the new one had no router at
     * all, and convergence could not see the difference.
     */
    public function execute(Store $store, string $domain): StoreDomain
    {
        $domain = StoreDomain::normalizeDomain($domain);

        Validator::make(
            ['domain' => $domain],
            ['domain' => [
                ...StoreDomain::activeDomainRules(),

                /*
                 | A deployment's domain is unique too, and it is the column the
                 | routing label is built from. Without this the collision
                 | surfaces from the database mid-transaction as a query error in
                 | the panel, rather than as a message on the field.
                 */
                Rule::unique(StorefrontDeployment::class, 'domain')->ignore($store->getKey(), 'store_id'),
            ]],
        )->validate();

        /*
         | Read before the transaction, written inside it. A store owns at most
         | one storefront, and this is the model the write below and the dispatch
         | afterwards both act on — held as an object rather than reassigned out
         | of the closure, because the arrow function wrapping the transaction
         | captures by value and a reference through it would not survive.
         */
        $deployment = StorefrontDeployment::query()
            ->where('store_id', $store->getKey())
            ->first();

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
