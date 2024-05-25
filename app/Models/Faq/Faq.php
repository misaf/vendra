<?php

declare(strict_types=1);

namespace App\Models\Faq;

use App\Casts\DateCast;
use App\Models\Tenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Translatable\HasTranslations;

final class Faq extends Tenant implements HasMedia, Sortable
{
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
        'id'              => 'integer',
        'faq_category_id' => 'integer',
        'name'            => 'array',
        'description'     => 'array',
        'slug'            => 'array',
        'position'        => 'integer',
        'status'          => 'boolean',
        'created_at'      => DateCast::class,
        'updated_at'      => DateCast::class,
        'deleted_at'      => DateCast::class,
    ];

    protected $fillable = [
        'faq_category_id',
        'name',
        'description',
        'slug',
        'position',
        'status',
    ];

    public function faqCategory(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class);
    }

    public function multimedia(): MorphMany
    {
        return $this->media();
    }
}
