<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Casts\DateCast;
use App\Casts\MoneyCast;
use App\Models\Currency\Currency;
use App\Traits\BelongsToTenant;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

final class ProductPrice extends Model
{
    use BelongsToTenant;

    use HasFactory;

    use SoftDeletes;

    use TraitsBelongsToThrough;

    protected $casts = [
        'id'          => 'integer',
        'product_id'  => 'integer',
        'currency_id' => 'integer',
        'price'       => MoneyCast::class,
        'created_at'  => DateCast::class,
        'updated_at'  => DateCast::class,
        'deleted_at'  => DateCast::class,
    ];

    protected $fillable = [
        'product_id',
        'currency_id',
        'price',
    ];

    protected $with = ['currency'];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\Currency\Currency::class,
        );
    }

    public function currencyCategory(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\Currency\CurrencyCategory::class,
            through: \App\Models\Currency\Currency::class,
        );
    }

    public function getFormattedPrice()
    {
        // $formatter = new \NumberFormatter('en_US', \NumberFormatter::CURRENCY);
        // $formatter->setSymbol(\NumberFormatter::CURRENCY_SYMBOL, 'ریال');
        // $formatter->setSymbol(\NumberFormatter::MONETARY_GROUPING_SEPARATOR_SYMBOL, ',');
        // $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $this->price->getCurrency()->getDefaultFractionDigits());

        // return Money::ofMinor($this->price->getMinorAmount(), 'IRR')->formatWith($formatter);

        return number_format($this->price->getAmount()->toFloat(), $this->price->getCurrency()->getDefaultFractionDigits());
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

    protected static function booted(): void
    {
        static::creating(fn(\App\Models\Product\ProductPrice $productPrice) => $productPrice->currency_id = Currency::where('slug', 'toman')->value('id'));
    }
}
