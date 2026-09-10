<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraMultimedia\Concerns\HasDefaultMediaConversions;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraTransaction\Database\Factories\TransactionGatewayFactory;
use Misaf\VendraTransaction\Observers\TransactionGatewayObserver;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * An admin-managed payment channel label. The scalar `slug` is the stable
 * programmatic identifier; actual payment processing lives outside this
 * module.
 *
 * @property int $id
 * @property int $tenant_id
 * @property array<string, string> $name
 * @property array<string, string>|null $description
 * @property string $slug
 * @property int $position
 * @property bool $active
 * @property bool $is_default
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'description', 'slug', 'position', 'active', 'is_default'])]
#[Hidden(['tenant_id', 'default_guard'])]
#[ObservedBy([TransactionGatewayObserver::class])]
#[UseFactory(TransactionGatewayFactory::class)]
final class TransactionGateway extends Model implements HasMedia, ShouldLogActivity, Sortable
{
    use BelongsToTenant;
    use HasDefaultMediaConversions, InteractsWithMedia {
        HasDefaultMediaConversions::registerMediaConversions insteadof InteractsWithMedia;
    }

    /** @use HasFactory<TransactionGatewayFactory> */
    use HasFactory;

    use HasSlug;
    use HasTranslations;
    use SoftDeletes;
    use SortableTrait;

    public const string MEDIA_COLLECTION = 'transaction-gateways';

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
     * @var array<int, string>
     */
    public array $translatable = ['name', 'description'];

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_default' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'name' => 'array',
            'description' => 'array',
            'slug' => 'string',
            'position' => 'integer',
            'active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Transaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * @return MorphMany<Media, $this>
     */
    public function multimedia(): MorphMany
    {
        return $this->media();
    }

    /**
     * @param  Builder<self>  $builder
     */
    public function scopeActive(Builder $builder): void
    {
        $builder->where('active', true);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $gateway): string => $gateway->getTranslation('name', config()->string('app.fallback_locale', 'en')))
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }
}
