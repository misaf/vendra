<?php

declare(strict_types=1);

namespace Misaf\Currency\Models;

use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\Currency\Database\Factories\CurrencyFactory;
use Misaf\Tenant\Traits\BelongsToTenant;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $currency_category_id
 * @property string $name
 * @property string $description
 * @property string $slug
 * @property string $iso_code
 * @property float $conversion_rate
 * @property int $decimal_place
 * @property int $buy_price
 * @property int $sell_price
 * @property bool $is_default
 * @property int $position
 * @property bool $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon $deleted_at
 */
final class Currency extends Model implements HasMedia, Sortable
{
    use BelongsToTenant;
    /** @use HasFactory<CurrencyFactory> */
    use HasFactory;
    use HasSlugOptionsTrait;
    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }
    use LogsActivity;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'id'                   => 'integer',
        'tenant_id'            => 'integer',
        'currency_category_id' => 'integer',
        'name'                 => 'string',
        'description'          => 'string',
        'slug'                 => 'string',
        'iso_code'             => 'string',
        'conversion_rate'      => 'float',
        'decimal_place'        => 'integer',
        'buy_price'            => 'integer',
        'sell_price'           => 'integer',
        'is_default'           => 'boolean',
        'position'             => 'integer',
        'status'               => 'boolean',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'currency_category_id',
        'name',
        'description',
        'slug',
        'iso_code',
        'conversion_rate',
        'decimal_place',
        'buy_price',
        'sell_price',
        'is_default',
        'position',
        'status',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'tenant_id',
    ];

    /**
     * @return BelongsTo<CurrencyCategory, $this>
     */
    public function currencyCategory(): BelongsTo
    {
        return $this->belongsTo(CurrencyCategory::class);
    }

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
}
