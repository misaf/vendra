<?php

declare(strict_types=1);

namespace App\Models\Order;

use App\Casts\DateCast;
use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
use App\Models\Product\Product;
use App\Models\Product\ProductCategory;
use App\Models\Scopes\Tenant as TenantScope;
use App\Models\User\User;
use App\Traits\ActivityLog;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

#[ScopedBy(TenantScope::class)]
final class OrderProduct extends Model
{
    use ActivityLog;

    use BelongsToTenant;

    use HasFactory;

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

    public function currency(): BelongsToThrough
    {
        return $this->belongsToThrough(Currency::class, Order::class);
    }

    public function CurrencyCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(CurrencyCategory::class, [
            Currency::class,
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
        return $this->belongsToThrough(User::class, Order::class);
    }
}
