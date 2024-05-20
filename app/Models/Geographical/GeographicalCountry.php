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
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

#[ScopedBy([\App\Scopes\Tenant::class])]
final class GeographicalCountry extends Model implements HasMedia
{
    use BelongsToTenant;

    use HasFactory;

    use HasRelationships;

    use HasSlugOptionsTrait;

    use InteractsWithMedia, ThumbnailTableRecord {
        ThumbnailTableRecord::registerMediaCollections insteadof InteractsWithMedia;
        ThumbnailTableRecord::registerMediaConversions insteadof InteractsWithMedia;
    }

    use LogsActivity;

    use SoftDeletes;

    protected $casts = [
        'id'                   => 'integer',
        'geographical_zone_id' => 'integer',
        'name'                 => 'string',
        'description'          => 'string',
        'slug'                 => 'string',
        'status'               => 'boolean',
        'created_at'           => DateCast::class,
        'updated_at'           => DateCast::class,
        'deleted_at'           => DateCast::class,
    ];

    protected $fillable = [
        'geographical_zone_id',
        'name',
        'description',
        'slug',
        'status',
    ];

    public function geographicalCities(): HasManyThrough
    {
        return $this->hasManyThrough(
            related: \App\Models\Geographical\GeographicalCity::class,
            through: \App\Models\Geographical\GeographicalState::class,
        );
    }

    public function geographicalNeighborhoods(): HasManyDeep
    {
        return $this->hasManyDeep(
            related: \App\Models\Geographical\GeographicalNeighborhood::class,
            through: [
                \App\Models\Geographical\GeographicalState::class,
                \App\Models\Geographical\GeographicalCity::class,
                \App\Models\Geographical\GeographicalCountry::class,
            ],
        );
    }

    public function geographicalStates(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Geographical\GeographicalState::class,
        );
    }

    public function geographicalZone(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\Geographical\GeographicalZone::class,
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logExcept(['id']);
    }
}
