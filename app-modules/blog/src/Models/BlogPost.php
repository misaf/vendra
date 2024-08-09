<?php

declare(strict_types=1);

namespace Termehsoft\Blog\Models;

use App\Casts\DateCast;
use App\Models\BaseModelWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Translatable\HasTranslations;
use Termehsoft\Blog\Observers\BlogPostObserver;

#[ObservedBy([BlogPostObserver::class])]
final class BlogPost extends BaseModelWithMedia implements
    Sortable
{
    use HasSlugOptionsTrait;
    use HasTranslatableSlug;
    use HasTranslations;
    use SoftDeletes;
    use SortableTrait;

    public $translatable = ['name', 'description', 'slug'];

    protected $casts = [
        'id'                    => 'integer',
        'blog_post_category_id' => 'integer',
        'name'                  => 'array',
        'description'           => 'array',
        'slug'                  => 'array',
        'position'              => 'integer',
        'status'                => 'boolean',
        'created_at'            => DateCast::class,
        'updated_at'            => DateCast::class,
        'deleted_at'            => DateCast::class,
    ];

    protected $fillable = [
        'blog_post_category_id',
        'name',
        'description',
        'slug',
        'position',
        'status',
    ];

    public function blogPostCategory(): BelongsTo
    {
        return $this->belongsTo(BlogPostCategory::class);
    }
}
