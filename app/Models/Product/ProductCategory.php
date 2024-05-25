<?php

declare(strict_types=1);

namespace App\Models\Product;

use App\Casts\DateCast;
use App\Models\Order\OrderProduct;
use App\Models\Tenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Translatable\HasTranslations;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

final class ProductCategory extends Tenant implements HasMedia, Sortable
{
    use HasRecursiveRelationships;

    use HasSlugOptionsTrait;

    use HasTranslatableSlug;

    use HasTranslations;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    use SoftDeletes;

    use SortableTrait;

    public $translatable = ['name', 'description', 'slug'];

    protected $casts = [
        'id'          => 'integer',
        'parent_id'   => 'integer',
        'name'        => 'array',
        'description' => 'array',
        'slug'        => 'array',
        'position'    => 'integer',
        'status'      => 'boolean',
        'created_at'  => DateCast::class,
        'updated_at'  => DateCast::class,
        'deleted_at'  => DateCast::class,
    ];

    protected $fillable = [
        'parent_id',
        'name',
        'description',
        'slug',
        'position',
        'status',
    ];

    public function multimedia(): MorphMany
    {
        return $this->media();
    }

    public function orderProducts(): HasManyThrough
    {
        return $this->hasManyThrough(OrderProduct::class, Product::class);
    }

    public function productPrices(): HasManyThrough
    {
        return $this->hasManyThrough(ProductPrice::class, Product::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
