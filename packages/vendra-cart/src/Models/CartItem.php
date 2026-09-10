<?php

declare(strict_types=1);

namespace Misaf\VendraCart\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Misaf\VendraCart\Database\Factories\CartItemFactory;

/**
 * @property int $id
 * @property int $cart_id
 * @property string $sellable_type
 * @property int $sellable_id
 * @property int $quantity
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['cart_id', 'sellable_type', 'sellable_id', 'quantity', 'metadata'])]
#[UseFactory(CartItemFactory::class)]
final class CartItem extends Model
{
    /** @use HasFactory<CartItemFactory> */
    use HasFactory;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'quantity' => 1,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'cart_id' => 'integer',
            'sellable_id' => 'integer',
            'quantity' => 'integer',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Cart, $this>
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function sellable(): MorphTo
    {
        return $this->morphTo();
    }
}
