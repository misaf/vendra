<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Scopes\StoreScope;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * Reusable domain data uses {@see BelongsToTenant} instead. The trait adds a
 * global {@see StoreScope}, so `StorefrontDeployment` avoids it to allow
 * fleet-wide sweeps.
 */
trait BelongsToStore
{
    /**
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    protected static function bootBelongsToStore(): void
    {
        static::addGlobalScope(new StoreScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('store_id') !== null) {
                return;
            }

            if ($storeId = resolve(TenantResolver::class)->currentId()) {
                $model->setAttribute('store_id', $storeId);
            }
        });
    }

    protected function initializeBelongsToStore(): void
    {
        $this->mergeCasts(['store_id' => 'integer']);
    }
}
