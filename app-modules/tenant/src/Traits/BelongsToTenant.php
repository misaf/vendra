<?php

declare(strict_types=1);

namespace Termehsoft\Tenant\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Termehsoft\Tenant;

trait BelongsToTenant
{
    /**
     * @return BelongsTo
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant\Models\Tenant::class);
    }

    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new Tenant\Scopes\Tenant());

        static::creating(function ($model): void {
            if (app()->has('currentTenant')) {
                $model->tenant_id = app('currentTenant')->id;
            }
        });
    }
}
