<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Models;

use App\Casts\DateCast;
use App\Traits\HasSlugOptionsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Termehsoft\Tenant\Models\TenantWithMedia;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitBelongsToThrough;

final class GeographicalNeighborhood extends TenantWithMedia
{
    use HasSlugOptionsTrait;
    use SoftDeletes;
    use TraitBelongsToThrough;

    protected $casts = [
        'id'                    => 'integer',
        'geographical_city_id'  => 'integer',
        'name'                  => 'string',
        'description'           => 'string',
        'slug'                  => 'string',
        'status'                => 'boolean',
        'created_at'            => DateCast::class,
        'updated_at'            => DateCast::class,
        'deleted_at'            => DateCast::class,
    ];

    protected $fillable = [
        'geographical_city_id',
        'name',
        'description',
        'slug',
        'status',
    ];

    public function geographicalCity(): BelongsTo
    {
        return $this->belongsTo(GeographicalCity::class);
    }

    public function geographicalCountry(): BelongsToThrough
    {
        return $this->belongsToThrough(GeographicalCountry::class, [
            GeographicalState::class,
            GeographicalCity::class,
        ]);
    }

    public function geographicalState(): BelongsToThrough
    {
        return $this->belongsToThrough(GeographicalState::class, GeographicalCity::class);
    }

    public function geographicalZone(): BelongsToThrough
    {
        return $this->belongsToThrough(GeographicalZone::class, [
            GeographicalCountry::class,
            GeographicalState::class,
            GeographicalCity::class,
        ]);
    }
}
