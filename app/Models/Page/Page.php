<?php

declare(strict_types=1);

namespace App\Models\Page;

use App\Models\Scopes\Tenant as TenantScope;
use App\Traits\BelongsToTenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

#[ScopedBy(TenantScope::class)]
final class Page extends Model implements HasMedia
{
    use BelongsToTenant;

    use HasFactory;

    use HasSlugOptionsTrait;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    protected $casts = [
        'id'               => 'integer',
        'page_category_id' => 'integer',
        'name'             => 'string',
        'description'      => 'string',
        'slug'             => 'string',
        'status'           => 'boolean',
    ];

    protected $fillable = [
        'page_category_id',
        'name',
        'description',
        'slug',
        'status',
    ];

    public function pageCategory(): BelongsTo
    {
        return $this->belongsTo(PageCategory::class);
    }
}
