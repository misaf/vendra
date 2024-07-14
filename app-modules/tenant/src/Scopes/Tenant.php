<?php

declare(strict_types=1);

namespace Termehsoft\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class Tenant implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (app()->has('currentTenant')) {
            $builder->where($model->getTable() . '.tenant_id', app('currentTenant')->id);
        }
    }
}
