<?php

declare(strict_types=1);

namespace App\Models\Geographical;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Traits\BelongsToTenant;
use App\Traits\HasSlugOptionsTrait;
use App\Traits\ThumbnailTableRecord;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

#[ScopedBy(TenantScope::class)]
final class GeographicalZone extends Model implements HasMedia
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
        'id'          => 'integer',
        'name'        => 'string',
        'description' => 'string',
        'slug'        => 'string',
        'status'      => 'boolean',
        'created_at'  => DateCast::class,
        'updated_at'  => DateCast::class,
        'deleted_at'  => DateCast::class,
    ];

    protected $fillable = [
        'name',
        'description',
        'slug',
        'status',
    ];

    public function geographicalCities(): HasManyDeep
    {
        return $this->hasManyDeep(GeographicalCity::class, [
            GeographicalCountry::class,
            GeographicalState::class,
        ]);
    }

    public function geographicalCountries(): HasMany
    {
        return $this->hasMany(GeographicalCountry::class);
    }

    public function geographicalNeighborhoods(): HasManyDeep
    {
        return $this->hasManyDeep(GeographicalNeighborhood::class, [
            GeographicalCountry::class,
            GeographicalState::class,
            GeographicalCity::class,
        ]);
    }

    public function geographicalStates(): HasManyThrough
    {
        return $this->hasManyThrough(GeographicalState::class, GeographicalCountry::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logExcept(['id']);
    }
}
