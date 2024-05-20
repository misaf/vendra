<?php

declare(strict_types=1);

namespace App\Models\Page;

use App\Traits\BelongsToTenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ScopedBy([\App\Scopes\Tenant::class])]
final class PageCategory extends Model implements HasMedia
{
    use BelongsToTenant;

    use HasFactory;

    use HasSlugOptionsTrait;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $casts = [
        'id'          => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'slug'        => 'string',
        'status'      => 'boolean',
    ];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'status',
    ];

    public function pages(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Page\Page::class,
        );
    }
}
