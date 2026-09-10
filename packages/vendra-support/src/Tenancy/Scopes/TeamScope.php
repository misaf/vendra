<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantSchema;

/**
 * Falls back to the signed-in user's own tenant when no tenant context has been
 * established — a panel-level convenience for surfaces that authenticate a user
 * before (or without) resolving a tenant from the request.
 *
 * This is not a second tenancy boundary: it reads the very same tenant foreign
 * key as {@see TenantScope} and never narrows below it. The historical "team"
 * name is kept because it predates the Store rename and describes the
 * user-membership fallback, not a Vendor/Team entity inside a Store.
 *
 * @implements Scope<Model>
 */
class TeamScope implements Scope
{
    /**
     * @param  Builder<covariant Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! TenantSchema::hasTenantColumn($model->getTable())) {
            return;
        }

        if (! app()->bound(TenantResolver::class) || app(TenantResolver::class)->current() !== null) {
            return;
        }

        $user = auth()->user();

        if (! $user instanceof Model) {
            return;
        }

        $foreignKey = TenantSchema::column();

        if (! array_key_exists($foreignKey, $user->getAttributes())) {
            return;
        }

        $tenantId = $user->getAttribute($foreignKey);

        if (is_int($tenantId) || is_string($tenantId)) {
            $builder->where($model->qualifyColumn($foreignKey), $tenantId);
        }
    }
}
