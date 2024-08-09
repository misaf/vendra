<?php

declare(strict_types=1);

namespace Termehsoft\Order\Models;

use App\Casts\DateCast;
use App\Models\BaseModel;
use App\Models\Currency;
use App\Models\Currency\CurrencyCategory;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Termehsoft\Order\Observers\OrderProductObserver;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

#[ObservedBy([OrderProductObserver::class])]
final class OrderProduct extends BaseModel implements
    User\Contracts\BelongsToUser,
    Currency\Contracts\BelongsToCurrency,
    Currency\Contracts\BelongsToCurrencyCategory
{
    use SoftDeletes;
    use TraitBelongsToThrough;

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

    /**
     * Get the user that owns the profile.
     *
     * @return BelongsToThrough
     */
    public function currency(): BelongsToThrough
    {
        return $this->belongsToThrough(Currency\Currency::class, Order::class);
    }

    /**
     * Get the user that owns the profile.
     *
     * @return BelongsToThrough
     */
    public function CurrencyCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(CurrencyCategory::class, [
            Currency\Currency::class,
            Order::class,
        ]);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(ProductCategory::class, Product::class);
    }

    public function user(): BelongsToThrough
    {
        return $this->belongsToThrough(User\User::class, Order::class);
    }
}
