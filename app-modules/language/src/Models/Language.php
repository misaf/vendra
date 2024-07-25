<?php

declare(strict_types=1);

namespace Termehsoft\Language\Models;

use App\Casts\DateCast;
use App\Models\BaseModelWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

final class Language extends BaseModelWithMedia implements
    Sortable
{
    use HasSlugOptionsTrait;
    use SoftDeletes;
    use SortableTrait;

    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'slug'        => 'string',
        'iso_code'    => 'string',
        'is_default'  => 'boolean',
        'position'    => 'integer',
        'status'      => 'boolean',
        'created_at'  => DateCast::class,
        'updated_at'  => DateCast::class,
        'deleted_at'  => DateCast::class,
    ];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'iso_code',
        'is_default',
        'position',
        'status',
    ];
}
