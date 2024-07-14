<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Models;

use App\Casts\DateCast;
use App\Models\Scopes\Tenant as TenantScope;
use App\Models\TenantWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

#[ScopedBy(TenantScope::class)]
final class GeographicalZone extends TenantWithMedia
{
    use HasRelationships;
    use HasSlugOptionsTrait;
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
}
