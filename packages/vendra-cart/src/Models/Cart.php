<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Misaf\VendraCart\Database\Factories\CartFactory;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string|null $owner_type
 * @property int|null $owner_id
 * @property-read string|null $owner_label
 * @property string $token
 * @property Carbon|null $expires_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['owner_type', 'owner_id', 'token', 'expires_at'])]
#[Hidden(['tenant_id'])]
#[UseFactory(CartFactory::class)]
final class Cart extends Model
{
    use BelongsToTenant;

    /** @use HasFactory<CartFactory> */
    use HasFactory;

    /**
     * Default the expiry from configuration unless the caller provided one,
     * including an explicit null for carts that should never expire.
     */
    protected static function booted(): void
    {
        self::creating(function (self $cart): void {
            if (array_key_exists('expires_at', $cart->getAttributes())) {
                return;
            }

            $days = Config::get('vendra-cart.expires_after_days');

            if (is_int($days)) {
                $cart->expires_at = now()->addDays($days);
            }
        });
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
            'expires_at' => 'datetime',
        ];
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

    /**
     * @return MorphTo<Model, $this>
     */
    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany<CartItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }
}
