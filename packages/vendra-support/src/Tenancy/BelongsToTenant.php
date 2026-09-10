<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Tenancy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\Scopes\TeamScope;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;

/**
 * Makes a model tenant-scoped without naming the tenant.
 *
 * Both things the trait needs — the foreign key and the owning model — come
 * from the bound {@see TenantResolver}, so nothing here names a business model.
 * Vendra keeps the neutral `products.tenant_id` and resolves it to a Store;
 * an application whose tenant is a Company configures `company_id` and gets the
 * same behaviour. `$model->tenant` returns whichever model is configured.
 *
 * The trait also hides and casts the foreign key, so models never repeat it in
 * `#[Hidden]` or `casts()`.
 */
trait BelongsToTenant
{
    /**
     * The owning tenant — a `Store` in Vendra, a `Company` or `Workspace`
     * elsewhere. The relation is deliberately named for the role and not for
     * the business model, because this trait lives in a package that must work
     * under any of them.
     *
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
