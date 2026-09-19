<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\Scopes\TeamScope;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;

/**
 * The trait also hides and casts the foreign key, so models do not repeat it.
 */
trait BelongsToTenant
{
    /**
     * @return BelongsTo<Model, $this>
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo($this->tenantModelClass(), TenantSchema::column());
    }

    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);
        static::addGlobalScope(new TeamScope);

        static::creating(function (Model $model): void {
            if (! TenantSchema::hasTenantColumn($model->getTable())) {
                return;
            }

            if ($tenantId = resolve(TenantResolver::class)->currentId()) {
                $model->setAttribute(TenantSchema::column(), $tenantId);
            }
        });
    }

    protected function initializeBelongsToTenant(): void
    {
        $column = TenantSchema::column();

        $this->mergeCasts([$column => 'integer']);
        $this->mergeHidden([$column]);
    }

    /**
     * @return class-string<Model>
     */
    private function tenantModelClass(): string
    {
        return resolve(TenantResolver::class)->modelClass();
    }
}
