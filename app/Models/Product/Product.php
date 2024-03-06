<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Models\Order\OrderProduct;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;

final class Product extends Model implements HasMedia, Sortable
{
    use HasFactory;

    use HasSlugOptionsTrait;

    use HasTags;

    use HasTranslatableSlug;

    use HasTranslations;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

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
        'availability_date'   => 'date',
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

    public function orderProducts(): HasMany
    {
        return $this->hasMany(OrderProduct::class);
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function productPrice(): HasOne
    {
        return $this->hasOne(ProductPrice::class)->latestOfMany();
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function scopeFilter(Builder $builder, array $filter): Builder
    {
        $builder->when($filter['search'] ?? false, fn(Builder $builder) => $builder->where('name->' . app()->getLocale(), 'LIKE', '%' . $filter['search'] . '%')
            ->orWhere('description->' . app()->getLocale(), 'LIKE', '%' . $filter['search'] . '%')->orWhere('token', 'LIKE', '%' . $filter['search'] . '%'));

        $builder->when($filter['category'] ?? false, fn(Builder $builder) => $builder->where('product_category_id', $filter['category']));

        $builder->when('cheapest' === $filter['sort'] ?? false, fn(Builder $builder) => $builder->latest(ProductPrice::select('price')->whereColumn('product_prices.product_id', 'products.id')->limit(1)));

        $builder->when('expensivest' === $filter['sort'] ?? false, fn(Builder $builder) => $builder->oldest(ProductPrice::select('price')->whereColumn('product_prices.product_id', 'products.id')->limit(1)));

        $builder->when('daily' === $filter['sort'] ?? false, fn(Builder $builder) => $builder->whereHas('productCategory', function (Builder $builder): void {
            $builder->where('product_categories.slug->' . app()->getLocale(), 'daily')
                ->where('product_categories.status', 'Enable');
        }));

        $builder->when('best_selling' === $filter['sort'] ?? false, fn(Builder $builder) => $builder->whereHas('productCategory', function (Builder $builder): void {
            $builder->where('product_categories.slug->' . app()->getLocale(), 'best-selling')
                ->where('product_categories.status', 'Enable');
        }));

        return $builder;
    }

    protected static function booted(): void
    {
        static::creating(fn(Product $product) => $product->token = fake()->randomNumber(9, true));
    }
}
