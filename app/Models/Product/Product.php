<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Casts\DateCast;
use App\Traits\BelongsToTenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Tags\HasTags;
use Spatie\Translatable\HasTranslations;

#[ScopedBy([\App\Scopes\Tenant::class])]
final class Product extends Model implements HasMedia, Sortable
{
    use BelongsToTenant;

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
        return $this->hasOne(
            related: \App\Models\Product\ProductPrice::class,
        )->latestOfMany();
    }

    public function multimedia(): MorphMany
    {
        return $this->media();
    }

    public function orderProducts(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Order\OrderProduct::class,
        );
    }

    public function productCategory(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\Product\ProductCategory::class,
        );
    }

    public function productPrices(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Product\ProductPrice::class,
        );
    }

    protected static function booted(): void
    {
        static::creating(fn(self $product): int => $product->token = fake()->randomNumber(9, true));
    }
}
