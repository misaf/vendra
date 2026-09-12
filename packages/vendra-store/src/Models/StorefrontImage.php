<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Misaf\VendraStore\Database\Factories\StorefrontImageFactory;

/**
 * An approved storefront artifact.
 *
 * @property int $id
 * @property string $image
 * @property string|null $notes
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['image', 'notes', 'active'])]
#[UseFactory(StorefrontImageFactory::class)]
final class StorefrontImage extends Model
{
    /** @use HasFactory<StorefrontImageFactory> */
    use HasFactory;

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /** @return HasMany<StorefrontDeployment, $this> */
    public function deployments(): HasMany
    {
        return $this->hasMany(StorefrontDeployment::class);
    }

    public function isInUse(): bool
    {
        return $this->deployments()->exists();
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
