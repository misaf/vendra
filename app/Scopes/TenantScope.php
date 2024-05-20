<?php

declare(strict_types=1);

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

final class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model   $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where($model->getTable() . '.tenant_id', 1);
        // if (auth()->check() && null !== auth()->user()->tenant_id) {
        //     $builder->where($model->getTable() . '.tenant_id', auth()->user()->tenant_id);
        // }
    }
}
