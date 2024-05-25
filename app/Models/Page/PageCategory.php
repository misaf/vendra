<?php

declare(strict_types=1);

namespace App\Models\Page;

use App\Models\Tenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class PageCategory extends Tenant implements HasMedia
{
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
        return $this->hasMany(Page::class);
    }
}
