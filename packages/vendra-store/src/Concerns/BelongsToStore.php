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
 * For records that describe a Store rather than live inside one.
 *
 * Data belonging to a reusable domain package (products, posts, roles) is owned
 * through the generic `tenant_id` column and
 * {@see BelongsToTenant}. Store-specific records name their owner outright with
 * `store_id` and use this instead.
 *
 * Carrying `store_id` is not on its own a reason to use this trait: it adds a
 * global {@see StoreScope}. `StorefrontDeployment` stays off it so the
 * fleet-wide commands can sweep every store's deployments in one pass.
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
