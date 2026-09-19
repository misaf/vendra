<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;

/**
 * Tenant-aware package data uses {@see TenantScope} on `tenant_id` instead.
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

        if ($storeId = resolve(TenantResolver::class)->currentId()) {
            $builder->where($model->qualifyColumn('store_id'), $storeId);
        }
    }
}
