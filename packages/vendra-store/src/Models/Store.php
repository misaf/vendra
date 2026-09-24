<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Laravel\Pennant\Concerns\HasFeatures;
use Misaf\VendraStore\Database\Factories\StoreFactory;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Observers\StoreObserver;
use Misaf\VendraStore\Scopes\StoreScope;
use Misaf\VendraStore\Support\StoreStatusCounts;
use Misaf\VendraSubscription\Contracts\SubscriptionSubscriber;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraTenant\Concerns\IsTenantModel;
use Misaf\VendraTenant\Contracts\TenantContract;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;
use Misaf\VendraUser\Models\User;
use Spatie\Multitenancy\Models\Tenant as SpatieTenant;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * A store is the tenant: domain data points at it through `tenant_id`.
 *
 * `reseller_id` is a bare key so this package does not depend on the reseller package.
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
     * Stores suspended for billing, whatever their other status.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function billingSuspended(Builder $query): Builder
    {
        return $query->whereNotNull('billing_suspended_at');
    }

    /**
     * Get the store's currency, falling back to the platform default.
     *
     * `locale` and `timezone` fall back through {@see IsTenantModel} instead.
     */
    public function resolvedCurrency(): string
    {
        return $this->stringSetting('currency') ?? Config::string('money.defaultCurrency');
    }

    public function metadata(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->metadata ?? [], $key, $default);
    }

    public function status(): StoreStatus
    {
        return StoreStatus::fromColumns($this->provisioning_status, $this->active, $this->billing_suspended_at !== null);
    }

    /**
     * Determine if suspension or offboarding keeps the storefront down.
     *
     * A store that is still provisioning is not held down.
     */
    public function keepsStorefrontDown(): bool
    {
        return $this->trashed() || $this->status() === StoreStatus::Suspended;
    }

    /**
     * Get the store's admin panel URL on `<slug>.admin.<central host>`.
     *
     * Always HTTPS, since the edge proxy terminates TLS.
     */
    public function adminUrl(): string
    {
        return 'https://'.$this->slug.'.admin.'.Config::string('vendra-tenant.central_host');
    }

    /**
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
     * @param  Builder<self>  $query
     * @param  list<StoreStatus>  $statuses
     * @return Builder<self>
     */
    #[Scope]
    protected function withAnyStatus(Builder $query, array $statuses): Builder
    {
        return $query->where(function (Builder $query) use ($statuses): void {
            foreach ($statuses as $status) {
                $query->orWhere(fn (Builder $query): Builder => $query->withStatus($status));
            }
        });
    }

    /**
     * Match the stores a reseller bills for; without one, match nothing.
     *
     * A null `reseller_id` marks a console-owned store, so filtering on a missing reseller's key would match every one of them.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function ownedBy(Builder $query, (Model&SubscriptionSubscriber)|null $reseller): Builder
    {
        if ($reseller === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('reseller_id', $reseller->getKey());
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function withDeploymentStatus(Builder $query, StorefrontDeploymentStatus $status): Builder
    {
        return $query->whereHas('storefrontDeployment', fn (Builder $deployment): Builder => $deployment->where('status', $status));
    }

    /**
     * Match the statuses {@see StoreStatusCounts::NEEDING_ATTENTION} counts, plus stores whose storefront deployment failed.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function needingAttention(Builder $query): Builder
    {
        return $query->where(fn (Builder $query): Builder => $query
            ->withAnyStatus(StoreStatusCounts::NEEDING_ATTENTION)
            ->orWhere(fn (Builder $query): Builder => $query->withDeploymentStatus(StorefrontDeploymentStatus::Failed)));
    }

    /**
     * @return HasMany<StoreDomain, $this>
     */
    public function storeDomains(): HasMany
    {
        return $this->hasMany(StoreDomain::class);
    }

    /**
     * Get the store's domains without {@see StoreScope}, so other panels can read them.
     *
     * @return HasMany<StoreDomain, $this>
     */
    public function domains(): HasMany
    {
        return $this->storeDomains()->withoutGlobalScope(StoreScope::class);
    }

    /**
     * @return HasOne<StorefrontDeployment, $this>
     */
    public function storefrontDeployment(): HasOne
    {
        return $this->hasOne(StorefrontDeployment::class)
            ->withoutGlobalScope(StoreScope::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Get a string attribute, treating blank as unset.
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
