<?php

declare(strict_types=1);

namespace Misaf\VendraWishlist\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Misaf\VendraWishlist\Database\Factories\WishlistItemFactory;

/**
 * @property int $id
 * @property int $wishlist_id
 * @property string $sellable_type
 * @property int $sellable_id
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['wishlist_id', 'sellable_type', 'sellable_id', 'metadata'])]
#[UseFactory(WishlistItemFactory::class)]
final class WishlistItem extends Model
{
    /** @use HasFactory<WishlistItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'wishlist_id' => 'integer',
            'sellable_id' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Wishlist, $this>
     */
    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function sellable(): MorphTo
    {
        return $this->morphTo();
    }
}
