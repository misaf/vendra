<?php

declare(strict_types=1);

namespace App\Models\Currency;

use App\Casts\DateCast;
use App\Models\Tenant;
use App\Models\User;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Currency extends Tenant implements
    Contracts\BelongsToCurrencyCategory,
    HasMedia,
    Sortable,
    User\Contracts\HasUserProfileBalance
{
    use HasSlugOptionsTrait;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    use SoftDeletes;

    use SortableTrait;

    use Traits\BelongsToCurrencyCategory;

    use User\Traits\HasUserProfileBalance;

    protected $casts = [
        'id'                   => 'integer',
        'currency_category_id' => 'integer',
        'name'                 => 'string',
        'description'          => 'string',
        'slug'                 => 'string',
        'iso_code'             => 'string',
        'conversion_rate'      => 'float',
        'decimal_place'        => 'integer',
        'is_default'           => 'boolean',
        'position'             => 'integer',
        'status'               => 'boolean',
        'created_at'           => DateCast::class,
        'updated_at'           => DateCast::class,
        'deleted_at'           => DateCast::class,
    ];

    protected $fillable = [
        'currency_category_id',
        'name',
        'description',
        'slug',
        'iso_code',
        'conversion_rate',
        'decimal_place',
        'is_default',
        'position',
        'status',
    ];
}
