<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantSchema;

/**
 * @implements Scope<Model>
 */
class TenantScope implements Scope
{
    /**
     * @param  Builder<covariant Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! TenantSchema::hasTenantColumn($model->getTable())) {
            return;
        }

        if (app()->bound(TenantResolver::class) && $tenantId = resolve(TenantResolver::class)->currentId()) {
            $builder->where($model->qualifyColumn(TenantSchema::column()), $tenantId);
        }
    }
}
