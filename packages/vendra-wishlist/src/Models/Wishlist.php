<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraWishlist\Database\Factories\WishlistFactory;
use Misaf\VendraWishlist\Settings\WishlistSettings;

/**
 * A customer's saved items; one of their lists is the default.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string|null $owner_type
 * @property int|null $owner_id
 * @property-read string|null $owner_label
 * @property string $token
 * @property string $name
 * @property bool $is_default
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['owner_type', 'owner_id', 'token', 'name', 'is_default'])]
#[Hidden(['tenant_id', 'default_list_guard'])]
#[UseFactory(WishlistFactory::class)]
final class Wishlist extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<WishlistFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        self::creating(function (self $wishlist): void {
            if (! array_key_exists('token', $wishlist->getAttributes())) {
                $wishlist->token = (string) Str::uuid();
            }

            if (! array_key_exists('name', $wishlist->getAttributes())) {
                $wishlist->name = resolve(WishlistSettings::class)->default_name;
            }
        });
    }

    /**
     * A unique index allows one default list per owner, so concurrent first
     * saves resolve to the same list instead of creating two.
     */
    public static function firstOrCreateDefaultFor(Model $owner): self
    {
        return self::query()->firstOrCreate(
            [
                'owner_type' => $owner->getMorphClass(),
                'owner_id' => $owner->getKey(),
                'is_default' => true,
            ],
            [
                'token' => (string) Str::uuid(),
                'name' => resolve(WishlistSettings::class)->default_name,
            ],
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'owner_id' => 'integer',
            'token' => 'string',
            'name' => 'string',
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<WishlistItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function has(Model $sellable): bool
    {
        return $this->items()
            ->where('sellable_type', $sellable->getMorphClass())
            ->where('sellable_id', $sellable->getKey())
            ->exists();
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function ownerLabel(): Attribute
    {
        return Attribute::make(
            get: function (): ?string {
                $owner = $this->owner;

                if (! $owner instanceof Model) {
                    return null;
                }

                foreach (['username', 'name', 'email'] as $attribute) {
                    $value = $owner->getAttribute($attribute);

                    if (is_string($value) && $value !== '') {
                        return $value;
                    }
                }

                $routeKey = $owner->getRouteKey();

                return is_int($routeKey) || is_string($routeKey)
                    ? (string) $routeKey
                    : null;
            },
        );
    }
}
