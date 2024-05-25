<?php

declare(strict_types=1);

namespace App\Models\Language;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Traits\ActivityLog;
use App\Traits\BelongsToTenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ScopedBy(TenantScope::class)]
final class Language extends Model implements HasMedia, Sortable
{
    use ActivityLog;

    use BelongsToTenant;

    use HasFactory;

    use HasSlugOptionsTrait;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

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
