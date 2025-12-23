<?php

declare(strict_types=1);

namespace Misaf\Currency\Models;

use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\Currency\Database\Factories\CurrencyCategoryFactory;
use Misaf\Currency\Observers\CurrencyCategoryObserver;
use Misaf\Currency\Traits\HasCurrency as HasCurrencyTrait;
use Misaf\Tenant\Traits\BelongsToTenant;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\SlugOptions;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
#[ObservedBy([CurrencyCategoryObserver::class])]
final class CurrencyCategory extends Model implements HasMedia
{
    use BelongsToTenant;
    use HasCurrencyTrait;
    /** @use HasFactory<CurrencyCategoryFactory> */
    use HasFactory;
    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }
    use LogsActivity;
    use SoftDeletes;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'          => 'integer',
        'tenant_id'   => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'slug'        => 'string',
        'status'      => 'boolean',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'slug',
        'status',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'tenant_id',
    ];

    /**
     * @return MorphMany<Media, $this>
     */
    public function multimedia(): MorphMany
    {
        return $this->media();
    }

    /**
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logExcept(['id']);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->preventOverwrite();
    }
}
