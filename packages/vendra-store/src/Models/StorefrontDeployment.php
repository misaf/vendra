<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Misaf\VendraStore\Database\Factories\StorefrontDeploymentFactory;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Exceptions\InvalidStorefrontTransitionException;
use Misaf\VendraStore\Exceptions\StoreNotServingException;

/**
 * `desired_state` is the intent; `status`, `container_name`, and `image_digest`
 * are what was last observed.
 *
 * @property int $id
 * @property int $store_id
 * @property int|null $storefront_image_id
 * @property string $slug
 * @property string $domain
 * @property array<string, mixed> $configuration
 * @property StorefrontDeploymentStatus $status
 * @property StorefrontDesiredState $desired_state
 * @property string|null $container_name
 * @property string|null $image
 * @property string|null $image_digest
 * @property Carbon|null $requested_at
 * @property Carbon|null $deployed_at
 * @property Carbon|null $failed_at
 * @property string|null $error
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'store_id', 'storefront_image_id', 'slug', 'domain', 'configuration', 'status', 'desired_state',
    'container_name', 'image', 'image_digest', 'requested_at', 'deployed_at',
    'failed_at', 'error',
])]
#[UseFactory(StorefrontDeploymentFactory::class)]
final class StorefrontDeployment extends Model
{
    /** @use HasFactory<StorefrontDeploymentFactory> */
    use HasFactory;

    /**
     * Get the store, unscoped and including trashed stores.
     *
     * @return BelongsTo<Store, $this>
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class)->withTrashed();
    }

    /** @return BelongsTo<StorefrontImage, $this> */
    public function storefrontImage(): BelongsTo
    {
        return $this->belongsTo(StorefrontImage::class);
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function desiredRunning(Builder $query): Builder
    {
        return $query->where('desired_state', StorefrontDesiredState::Running->value);
    }

    public function url(): string
    {
        return 'https://'.$this->domain;
    }

    /**
     * Mark the deployment as processing, clearing any previous failure.
     */
    public function markProcessing(): void
    {
        $this->transitionTo(StorefrontDeploymentStatus::Processing, [
            'failed_at' => null,
            'error' => null,
        ]);
    }

    public function markReady(?string $containerName, ?string $image, ?string $imageDigest): void
    {
        $this->transitionTo(StorefrontDeploymentStatus::Ready, [
            'container_name' => $containerName,
            'image' => $image,
            'image_digest' => $imageDigest,
            'requested_at' => now(),
            'deployed_at' => now(),
        ]);
    }

    /**
     * Mark the deployment as placed but unverified, for reconciliation to revisit.
     */
    public function markRequested(?string $containerName, ?string $image, ?string $imageDigest): void
    {
        $this->transitionTo(StorefrontDeploymentStatus::Requested, [
            'container_name' => $containerName,
            'image' => $image,
            'image_digest' => $imageDigest,
            'requested_at' => now(),
            'deployed_at' => null,
        ]);
    }

    /**
     * Mark the deployment as failed once the queue has exhausted its attempts.
     */
    public function markFailed(string $error): void
    {
        $this->transitionTo(StorefrontDeploymentStatus::Failed, [
            'failed_at' => now(),
            'error' => Str::limit($error, 2000, ''),
        ]);
    }

    /**
     * Record the intended state without touching the deployment status.
     */
    public function markDesiredState(StorefrontDesiredState $state): void
    {
        $this->forceFill(['desired_state' => $state])->save();
    }

    /**
     * Get the columns that describe the requested storefront.
     *
     * Jobs compare it before and after running to catch a change whose dispatch
     * the unique lock discarded.
     *
     * @return array<string, mixed>
     */
    public function intentFingerprint(): array
    {
        return [
            'slug' => $this->slug,
            'domain' => $this->domain,
            'storefront_image_id' => $this->storefront_image_id,
            'configuration' => $this->configuration,
            'desired_state' => $this->desired_state->value,
        ];
    }

    /**
     * Determine if the storefront's store, including a trashed one, may serve.
     *
     * Reuses an eager-loaded store, so a table row asks without a query.
     */
    public function storeMayServe(): bool
    {
        return self::mayServe($this->relationLoaded('store') ? $this->store : $this->store()->first());
    }

    /**
     * Re-read the store rather than trusting a loaded one, since an action
     * must not run on a store suspended after the record was loaded.
     *
     * @throws StoreNotServingException
     */
    public function assertStoreMayServe(): void
    {
        throw_unless(self::mayServe($this->store()->first()), StoreNotServingException::forDeployment($this));
    }

    private static function mayServe(?Store $store): bool
    {
        return ! $store instanceof Store || ! $store->keepsStorefrontDown();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'store_id' => 'integer',
            'storefront_image_id' => 'integer',
            'configuration' => 'array',
            'status' => StorefrontDeploymentStatus::class,
            'desired_state' => StorefrontDesiredState::class,
            'requested_at' => 'datetime',
            'deployed_at' => 'datetime',
            'failed_at' => 'datetime',
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     *
     * @throws InvalidStorefrontTransitionException
     */
    private function transitionTo(StorefrontDeploymentStatus $status, array $attributes): void
    {
        if (! $this->status->canTransitionTo($status)) {
            throw InvalidStorefrontTransitionException::between($this->status, $status);
        }

        $this->forceFill([...$attributes, 'status' => $status])->save();
    }
}
