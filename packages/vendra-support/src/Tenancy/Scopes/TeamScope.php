<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantSchema;

/**
 * Uses the same foreign key as {@see TenantScope}; "team" is a historical name.
 *
 * @implements Scope<Model>
 */
final class TeamScope implements Scope
{
    /**
     * @param  Builder<covariant Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (! TenantSchema::hasTenantColumn($model->getTable())) {
            return;
        }

        if (! app()->bound(TenantResolver::class) || resolve(TenantResolver::class)->current() !== null) {
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
