<?php

declare(strict_types=1);

namespace App\Scopes;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

final class Tenant implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        // dd(auth()->user());
        // if (auth()->check()) {
        // $tenantId = auth()->user()->tenant_id;
        // $builder->where($model->getTable() . '.tenant_id', $tenantId);
        // }
    }
}
