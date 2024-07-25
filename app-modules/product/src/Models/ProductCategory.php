<?php

declare(strict_types=1);

namespace Termehsoft\Product\Models;

use App\Casts\DateCast;
use App\Models\BaseModelWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Translatable\HasTranslations;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Termehsoft\Order\Models\OrderProduct;

final class ProductCategory extends BaseModelWithMedia implements
    Sortable
{
    use HasRecursiveRelationships;
    use HasSlugOptionsTrait;
    use HasTranslatableSlug;
    use HasTranslations;
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
