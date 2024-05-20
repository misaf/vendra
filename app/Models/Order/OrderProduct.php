<?php

declare(strict_types=1);

namespace App\Models\Order;

use App\Casts\DateCast;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

final class OrderProduct extends Model
{
    use BelongsToTenant;

    use HasFactory;

    use SoftDeletes;

    use TraitsBelongsToThrough;

    protected $casts = [
        'id'              => 'integer',
        'order_id'        => 'integer',
        'product_id'      => 'integer',
        'quantity'        => 'integer',
        'unit_price'      => 'integer',
        'tax_amount'      => 'integer',
        'discount_amount' => 'integer',
        'created_at'      => DateCast::class,
        'updated_at'      => DateCast::class,
        'deleted_at'      => DateCast::class,
    ];

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'tax_amount',
        'discount_amount',
    ];

    public function currency(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\Currency\Currency::class,
            through: \App\Models\Order\Order::class,
        );
    }

    public function CurrencyCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\Currency\CurrencyCategory::class,
            through: [
                \App\Models\Currency\Currency::class,
                \App\Models\Order\Order::class,
            ],
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\Order\Order::class,
        );
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\Product\Product::class,
        );
    }

    public function productCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\Product\ProductCategory::class,
            through: \App\Models\Product\Product::class,
        );
    }

    public function user(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\User::class,
            through: \App\Models\Order\Order::class,
        );
    }
}
