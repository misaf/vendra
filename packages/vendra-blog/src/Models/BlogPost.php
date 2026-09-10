<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraBlog\Database\Factories\BlogPostFactory;
use Misaf\VendraMultimedia\Concerns\HasDefaultMediaConversions;
use Misaf\VendraSupport\Capabilities\HasOptionalTags;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasTranslatableSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $blog_post_category_id
 * @property array<string, string> $name
 * @property array<string, string> $description
 * @property array<string, string> $slug
 * @property int $position
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['blog_post_category_id', 'name', 'description', 'slug', 'position', 'active'])]
#[Hidden(['tenant_id'])]
#[UseFactory(BlogPostFactory::class)]
final class BlogPost extends Model implements HasMedia, ShouldLogActivity, Sortable
{
    use BelongsToTenant;
    use HasDefaultMediaConversions, InteractsWithMedia {
        HasDefaultMediaConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    /** @use HasFactory<BlogPostFactory> */
    use HasFactory;

    use HasOptionalTags;
    use HasTranslatableSlug;
    use HasTranslations;
    use SoftDeletes;
    use SortableTrait;

    public const string TAG_TYPE = 'blog';

    public const string MEDIA_COLLECTION = 'blogs/posts';

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description', 'slug'];

    /**
     * Pin sortable behavior regardless of the global `eloquent-sortable`
     * configuration values: order on the `position` column and always assign
     * the next position when creating.
     *
     * Note: `ignore_timestamps` cannot be pinned here because the package reads
     * it directly from config (no per-model override), and it already defaults
     * to `false` both in config and in the package.
     *
     * @var array{order_column_name: string, sort_when_creating: bool}
     */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'blog_post_category_id' => 'integer',
            'name' => 'array',
            'description' => 'array',
            'slug' => 'array',
            'position' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<BlogPostCategory, $this>
     */
    public function blogPostCategory(): BelongsTo
    {
        return $this->belongsTo(BlogPostCategory::class);
    }

    /**
     * @return MorphMany<Media, $this>
     */
    public function multimedia(): MorphMany
    {
        return $this->media();
    }

    protected function tagType(): string
    {
        return self::TAG_TYPE;
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }
}
