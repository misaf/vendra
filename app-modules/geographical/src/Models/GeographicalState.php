<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Models;

use App\Casts\DateCast;
use App\Models\BaseModelWithMedia;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

final class GeographicalState extends BaseModelWithMedia
{
    use HasSlugOptionsTrait;
    use SoftDeletes;
    use TraitBelongsToThrough;

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
        return $this->hasMany(GeographicalCity::class);
    }

    public function geographicalCountry(): BelongsTo
    {
        return $this->belongsTo(GeographicalCountry::class);
    }

    public function geographicalNeighborhoods(): HasManyThrough
    {
        return $this->hasManyThrough(GeographicalNeighborhood::class, GeographicalCity::class);
    }

    public function geographicalZone(): BelongsToThrough
    {
        return $this->belongsToThrough(GeographicalZone::class, GeographicalCountry::class);
    }
}
