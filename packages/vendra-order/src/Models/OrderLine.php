<?php

declare(strict_types=1);

namespace Misaf\VendraOrder\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Misaf\VendraOrder\Database\Factories\OrderLineFactory;
use Spatie\Translatable\HasTranslations;

/**
 * An immutable purchase snapshot. The translatable name, unit amount and
 * currency are copied from the catalog when the order is placed so later
 * catalog edits never rewrite what a customer bought.
 *
 * @property int $id
 * @property int $order_id
 * @property string $sellable_type
 * @property int $sellable_id
 * @property array<string, string> $name
 * @property string $currency_code
 * @property int $quantity
 * @property Money $unit_amount
 * @property Money $line_amount
 * @property array<string, mixed>|null $metadata
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable([
    'order_id',
    'sellable_type',
    'sellable_id',
    'name',
    'currency_code',
    'quantity',
    'unit_amount',
    'line_amount',
    'metadata',
])]
#[UseFactory(OrderLineFactory::class)]
final class OrderLine extends Model
{
    /** @use HasFactory<OrderLineFactory> */
    use HasFactory;

    use HasTranslations;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'quantity' => 1,
    ];

    /**
     * @var list<string>
     */
    public array $translatable = ['name'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'order_id' => 'integer',
            'sellable_id' => 'integer',
            'name' => 'array',
            'currency_code' => 'string',
            'quantity' => 'integer',
            'unit_amount' => MoneyIntegerCast::class.':currency_code',
            'line_amount' => MoneyIntegerCast::class.':currency_code',
            'metadata' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function sellable(): MorphTo
    {
        return $this->morphTo();
    }
}
