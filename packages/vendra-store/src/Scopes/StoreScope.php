<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;

/**
 * Confines a Store-owned record to the current store.
 *
 * This is not a second tenancy mechanism competing with
 * {@see TenantScope}: reusable, tenant-aware
 * packages are owned through the neutral `tenant_id` column and scoped by that
 * one. A handful of tables — a store's domains, its storefront deployment —
 * describe the Store itself rather than data inside it, so they carry an
 * explicit `store_id` and are scoped here. Both read the same current tenant,
 * because in Vendra the current tenant *is* the store.
 *
 * @implements Scope<Model>
 */
final class StoreScope implements Scope
{
    /**
     * @param  Builder<covariant Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! app()->bound(TenantResolver::class)) {
            return;
        }

        if ($storeId = app(TenantResolver::class)->currentId()) {
            $builder->where($model->qualifyColumn('store_id'), $storeId);
        }
    }
}
