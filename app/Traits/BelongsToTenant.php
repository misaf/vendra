<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Tenant\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToTenant
{
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(
            related: Tenant::class,
        );
    }

    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model): void {
            if (auth()->check() && null !== auth()->user()->tenant_id) {
                // $model->tenant_id = auth()->user()->tenant_id;
            }
        });
    }
}
