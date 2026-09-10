<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Concerns\HasFeatures;
use Misaf\VendraStore\Database\Factories\StoreFactory;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Observers\StoreObserver;
use Misaf\VendraStore\Scopes\StoreScope;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraTenant\Concerns\IsTenantModel;
use Misaf\VendraTenant\Contracts\TenantContract;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;
use Misaf\VendraUser\Models\User;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * A store: the ecommerce business entity, and the tenancy boundary.
 *
 * The Store *is* the tenant — it implements {@see TenantContract} rather than
 * pointing at a separate tenant row — so `stores` is the only table describing
 * it. Products, orders, customers and vendors all live inside one store; a
 * reseller may own several, and the platform console may own one directly
 * (`reseller_id` is nullable).
 *
 * Reusable domain packages stay tenant-agnostic and are owned through the
 * neutral `tenant_id` column, which resolves to a store because a store is what
 * plays the tenant role here. Only records describing the store itself — its
 * domains, its storefront deployment — name it outright with `store_id`.
 *
 * The reseller link is deliberately a bare key here: the reseller domain is a
 * layer above the store, and `misaf/vendra-reseller` supplies both sides of the
 * relationship so the store package stays installable without it.
 *
 * @property int $id
 * @property int|null $reseller_id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property bool $active
 * @property string|null $locale
 * @property string|null $currency
 * @property string|null $timezone
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $billing_suspended_at
 * @property TenantProvisioningStatus $provisioning_status
 * @property bool $provisioning_should_seed
 * @property Carbon|null $provisioning_seeded_at
 * @property Carbon|null $routes_cached_at
 * @property Carbon|null $provisioned_at
 * @property Carbon|null $provisioning_failed_at
 * @property string|null $provisioning_error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'reseller_id', 'name', 'description', 'slug', 'active', 'locale', 'currency', 'timezone',
    'metadata', 'provisioning_status', 'provisioning_should_seed',
])]
#[ObservedBy([StoreObserver::class])]
#[UseFactory(StoreFactory::class)]
final class Store extends SpatieTenant implements ShouldLogActivity, TenantContract
{
    /** @use HasFactory<StoreFactory> */
    use HasFactory;

    use HasFeatures;
    use HasSlug;
    use IsTenantModel;
    use SoftDeletes;

    /**
     * Cascade a store's domains through its own lifecycle so no orphaned domain
     * keeps resolving. The active domain (active = true) follows the store;
     * replaced history domains (active = false, already trashed) are left
     * untouched on soft delete and only purged on force delete. Each callback
     * runs in the store's own tenant context so {@see StoreScope} on the
     * domains resolves to this store, not whatever store is currently active in
     * the request.
     */

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'reseller_id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'slug' => 'string',
            'active' => 'boolean',
            'locale' => 'string',
            'currency' => 'string',
            'timezone' => 'string',
            'metadata' => 'array',
            'billing_suspended_at' => 'datetime',
            'provisioning_status' => TenantProvisioningStatus::class,
            'provisioning_should_seed' => 'boolean',
            'provisioning_seeded_at' => 'datetime',
            'routes_cached_at' => 'datetime',
            'provisioned_at' => 'datetime',
            'provisioning_failed_at' => 'datetime',
            'provisioning_error' => 'string',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function inactive(Builder $query): Builder
    {
        return $query->where('active', false);
    }

    /**
     * Limit the query to stores that may currently serve requests.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function accessible(Builder $query): Builder
    {
        return $query
            ->active()
            ->whereNull('billing_suspended_at')
            ->where('provisioning_status', TenantProvisioningStatus::Ready);
    }

    /**
     * The currency the store prices in, falling back to the platform default.
     *
     * Its siblings `locale` and `timezone` need no accessor here: the tenancy
     * engine reads them through {@see IsTenantModel}, which already treats a
     * blank column as "no opinion, keep the platform's".
     */
    public function resolvedCurrency(): string
    {
        return $this->stringSetting('currency') ?? Config::string('money.defaultCurrency');
    }

    /**
     * One platform annotation off {@see $metadata}, or the given default.
     */
    public function metadata(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->metadata ?? [], $key, $default);
    }

    /**
     * The store's condition as one value.
     *
     * Derived from the three columns that own it rather than stored, so the
     * reading can never disagree with them. Suspension outranks readiness: a
     * store an operator disabled, or one billing suspended, is suspended even
     * though it provisioned cleanly.
     */
    public function status(): StoreStatus
    {
        return match (true) {
            $this->provisioning_status === TenantProvisioningStatus::Pending => StoreStatus::Pending,
            $this->provisioning_status === TenantProvisioningStatus::Processing => StoreStatus::Provisioning,
            $this->provisioning_status === TenantProvisioningStatus::Failed => StoreStatus::Failed,
            ! $this->active, $this->billing_suspended_at !== null => StoreStatus::Suspended,
            default => StoreStatus::Active,
        };
    }

    /**
     * Limit the query to stores whose derived {@see status()} is the given one.
     *
     * The match arms above expressed as SQL, so a console filter and a status
     * badge cannot disagree about what "suspended" means.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function withStatus(Builder $query, StoreStatus $status): Builder
    {
        return match ($status) {
            StoreStatus::Pending => $query->where('provisioning_status', TenantProvisioningStatus::Pending),
            StoreStatus::Provisioning => $query->where('provisioning_status', TenantProvisioningStatus::Processing),
            StoreStatus::Failed => $query->where('provisioning_status', TenantProvisioningStatus::Failed),
            StoreStatus::Suspended => $query
                ->where('provisioning_status', TenantProvisioningStatus::Ready)
                ->where(fn (Builder $suspended): Builder => $suspended
                    ->where('active', false)
                    ->orWhereNotNull('billing_suspended_at')),
            StoreStatus::Active => $query->accessible(),
        };
    }

    /**
     * @return HasMany<StoreDomain, $this>
     */
    public function storeDomains(): HasMany
    {
        return $this->hasMany(StoreDomain::class);
    }

    /**
     * A store's domains are always scoped to itself by the relationship's
     * foreign key, so {@see StoreScope} (which targets the currently active
     * store) must be dropped to read them from another store's context such as
     * the console or reseller panels.
     *
     * @return HasMany<StoreDomain, $this>
     */
    public function domains(): HasMany
    {
        return $this->storeDomains()->withoutGlobalScope(StoreScope::class);
    }

    /**
     * The storefront the platform runs for this store, when one was requested.
     *
     * @return HasMany<StorefrontDeployment, $this>
     */
    public function storefrontDeployments(): HasMany
    {
        return $this->hasMany(StorefrontDeployment::class)
            ->withoutGlobalScope(StoreScope::class);
    }

    /**
     * Users who may access this store through the tenant membership pivot.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * The name of the store's active (resolving) domain, if any.
     */
    public function activeDomainName(): ?string
    {
        $name = $this->domains()->where('active', true)->value('name');

        return is_string($name) ? $name : null;
    }

    /**
     * A configured string attribute, or null when the store leaves it unset.
     *
     * Blank is treated as unset: a form that submits an empty select should
     * mean "follow the platform", not "this store speaks nothing".
     */
    private function stringSetting(string $attribute): ?string
    {
        $value = $this->getAttribute($attribute);

        return is_string($value) && mb_trim($value) !== '' ? mb_trim($value) : null;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }
}
