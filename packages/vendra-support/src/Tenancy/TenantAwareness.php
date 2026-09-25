<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rules\Unique;
use Misaf\VendraSupport\Contracts\TenantResolver;

final class TenantAwareness
{
    /**
     * Determine if a tenant provider, such as `misaf/vendra-tenant`, is bound.
     */
    public static function enabled(): bool
    {
        return resolve(TenantResolver::class)->available();
    }

    public static function currentId(): ?int
    {
        if (! self::enabled()) {
            return null;
        }

        return resolve(TenantResolver::class)->currentId();
    }

    public static function constrainUniqueRule(Unique $rule): Unique
    {
        $tenantId = self::currentId();

        if ($tenantId === null) {
            return $rule;
        }

        return $rule->where(TenantSchema::column(), $tenantId);
    }

    /**
     * Limit a query to the current tenant's rows, or to the tenantless rows outside a tenant.
     *
     * The tenant scope applies nothing outside a tenant, so a tenantless caller,
     * such as the console, would otherwise see every tenant's rows.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainToCurrentTenant(Builder $query): Builder
    {
        return self::constrainToTenant($query, self::currentId());
    }

    /**
     * Limit a query to the rows that share the model's tenant, or its lack of one.
     *
     * A model not yet saved has no tenant stamped, so it takes the current one,
     * as `BelongsToTenant` will stamp it.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    public static function constrainToTenantOf(Builder $query, Model $model): Builder
    {
        if (! self::enabled() || ! TenantSchema::hasTenantColumn($model->getTable())) {
            return $query;
        }

        $tenantId = $model->getAttribute(TenantSchema::column());

        if ($tenantId === null && ! $model->exists) {
            return self::constrainToTenant($query, self::currentId());
        }

        return self::constrainToTenant($query, is_numeric($tenantId) ? (int) $tenantId : null);
    }

    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @return Builder<TModel>
     */
    private static function constrainToTenant(Builder $query, ?int $tenantId): Builder
    {
        $model = $query->getModel();

        if (! self::enabled() || ! TenantSchema::hasTenantColumn($model->getTable())) {
            return $query;
        }

        $column = $model->qualifyColumn(TenantSchema::column());

        return $tenantId === null ? $query->whereNull($column) : $query->where($column, $tenantId);
    }
}
