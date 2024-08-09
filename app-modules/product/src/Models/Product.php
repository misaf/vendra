<?php

declare(strict_types=1);

namespace Termehsoft\Product\Models;

use App\Casts\DateCast;
use App\Models\BaseModelWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;
use Termehsoft\Order\Models\OrderProduct;
use Termehsoft\Product\Observers\ProductObserver;

#[ObservedBy([ProductObserver::class])]
final class Product extends BaseModelWithMedia implements
    Sortable
{
    use HasSlugOptionsTrait;
    use HasTags;
    use HasTranslatableSlug;
    use HasTranslations;
    use SoftDeletes;
    use SortableTrait;

    public $translatable = ['name', 'description', 'slug', 'tags'];

    protected $casts = [
        'id'                  => 'integer',
        'product_category_id' => 'integer',
        'name'                => 'array',
        'description'         => 'array',
        'slug'                => 'array',
        'token'               => 'string',
        'quantity'            => 'integer',
        'stock_threshold'     => 'integer',
        'in_stock'            => 'boolean',
        'available_soon'      => 'boolean',
        'availability_date'   => DateCast::class,
        'created_at'          => DateCast::class,
        'updated_at'          => DateCast::class,
        'deleted_at'          => DateCast::class,
    ];

    protected $fillable = [
        'product_category_id',
        'name',
        'description',
        'slug',
        'quantity',
        'stock_threshold',
        'in_stock',
        'available_soon',
        'availability_date',
    ];

    public function ggg()
    {
        return $this->getFirstMedia()->toHtml();
    }

    public function latestProductPrice(): HasOne
    {
        return $this->hasOne(ProductPrice::class)->latestOfMany();
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    protected static function booted(): void
    {
        static::creating(fn(self $product): string => $product->token = mb_substr(str_shuffle(str_repeat('123456789', 9)), 0, 9));
    }
}
