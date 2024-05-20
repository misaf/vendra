<?php

declare(strict_types=1);

namespace App\Models\Geographical;

use App\Casts\DateCast;
use App\Traits\BelongsToTenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

#[ScopedBy([\App\Scopes\Tenant::class])]
final class GeographicalState extends Model implements HasMedia
{
    use BelongsToTenant;

    use HasFactory;

    use HasSlugOptionsTrait;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    use LogsActivity;

    use SoftDeletes;

    use TraitsBelongsToThrough;

    protected $casts = [
        'id'                      => 'integer',
        'geographical_country_id' => 'integer',
        'name'                    => 'string',
        'description'             => 'string',
        'slug'                    => 'string',
        'status'                  => 'boolean',
        'created_at'              => DateCast::class,
        'updated_at'              => DateCast::class,
        'deleted_at'              => DateCast::class,
    ];

    protected $fillable = [
        'geographical_country_id',
        'name',
        'description',
        'slug',
        'status',
    ];

    public function geographicalCities(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Geographical\GeographicalCity::class,
        );
    }

    public function geographicalCountry(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\Geographical\GeographicalCountry::class,
        );
    }

    public function geographicalNeighborhoods(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: \App\Models\Geographical\GeographicalNeighborhood::class,
            through: \App\Models\Geographical\GeographicalCity::class,
        );
    }

    public function geographicalZone(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\Geographical\GeographicalZone::class,
            through: \App\Models\Geographical\GeographicalCountry::class,
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logExcept(['id']);
    }
}
