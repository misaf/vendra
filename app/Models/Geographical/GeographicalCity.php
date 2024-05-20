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
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Znck\Eloquent\Relations\BelongsToThrough;
use Znck\Eloquent\Traits\BelongsToThrough as TraitsBelongsToThrough;

#[ScopedBy([\App\Scopes\Tenant::class])]
final class GeographicalCity extends Model implements HasMedia
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
        'id'                    => 'integer',
        'geographical_state_id' => 'integer',
        'name'                  => 'string',
        'description'           => 'string',
        'slug'                  => 'string',
        'status'                => 'boolean',
        'created_at'            => DateCast::class,
        'updated_at'            => DateCast::class,
        'deleted_at'            => DateCast::class,
    ];

    protected $fillable = [
        'geographical_state_id',
        'name',
        'description',
        'slug',
        'status',
    ];

    public function geographicalCountry(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\Geographical\GeographicalCountry::class,
            through: \App\Models\Geographical\GeographicalState::class,
        );
    }

    public function geographicalNeighborhoods(): HasMany
    {
        return $this->hasMany(
            related: \App\Models\Geographical\GeographicalNeighborhood::class,
        );
    }

    public function geographicalState(): BelongsTo
    {
        return $this->belongsTo(
            related: \App\Models\Geographical\GeographicalState::class,
        );
    }

    public function geographicalZone(): BelongsToThrough
    {
        return $this->belongsToThrough(
            related: \App\Models\Geographical\GeographicalZone::class,
            through: [
                \App\Models\Geographical\GeographicalCountry::class,
                \App\Models\Geographical\GeographicalState::class,
            ],
        );
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logExcept(['id']);
    }
}
