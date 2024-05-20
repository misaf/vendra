<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Tenant\Tenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model): void {
            $model->tenant_id = 1;
            // if (auth()->check() && null !== auth()->user()->tenant_id) {
            //     $model->tenant_id = auth()->user()->tenant_id;
            // }
        });
    }
}
