<?php

declare(strict_types=1);

namespace App\Models\Geographical;

use App\Casts\DateCast;
use App\Models\TenantWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

final class GeographicalCountry extends TenantWithMedia
{
    use HasRelationships;
    use HasSlugOptionsTrait;
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
        return $this->hasManyThrough(GeographicalCity::class, GeographicalState::class);
    }

    public function geographicalNeighborhoods(): HasManyDeep
    {
        return $this->hasManyDeep(GeographicalNeighborhood::class, [
            GeographicalState::class,
            GeographicalCity::class,
            GeographicalCountry::class,
        ]);
    }

    public function geographicalStates(): HasMany
    {
        return $this->hasMany(GeographicalState::class);
    }

    public function geographicalZone(): BelongsTo
    {
        return $this->belongsTo(GeographicalZone::class);
    }
}
