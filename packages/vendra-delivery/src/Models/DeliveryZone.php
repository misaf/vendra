<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Models;

use Cknow\Money\Casts\MoneyIntegerCast;
use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraDelivery\Database\Factories\DeliveryZoneFactory;
use Misaf\VendraDelivery\Support\GeoDistance;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Translatable\HasTranslations;

/**
 * A delivery band measured from the studio it is anchored to. Bands are
 * ordered by `position` from the tightest radius outwards, and the first one
 * that still covers a dropped pin prices the delivery.
 *
 * @property int $id
 * @property int $tenant_id
 * @property array<string, string> $name
 * @property array<string, string>|null $description
 * @property float $origin_latitude
 * @property float $origin_longitude
 * @property float|null $max_distance_km
 * @property string $currency_code
 * @property Money $fee_amount
 * @property bool $requires_quote
 * @property int $position
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'name',
    'description',
    'origin_latitude',
    'origin_longitude',
    'max_distance_km',
    'currency_code',
    'fee_amount',
    'requires_quote',
    'position',
    'active',
])]
#[Hidden(['tenant_id'])]
#[UseFactory(DeliveryZoneFactory::class)]
final class DeliveryZone extends Model implements ShouldLogActivity, Sortable
{
    use BelongsToTenant;

    /** @use HasFactory<DeliveryZoneFactory> */
    use HasFactory;

    use HasTranslations;
    use SoftDeletes;
    use SortableTrait;

    /**
     * @var array{order_column_name: string, sort_when_creating: bool}
     */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'name' => 'array',
            'description' => 'array',
            'origin_latitude' => 'float',
            'origin_longitude' => 'float',
            'max_distance_km' => 'float',
            'currency_code' => 'string',
            'fee_amount' => MoneyIntegerCast::class.':currency_code',
            'requires_quote' => 'boolean',
            'position' => 'integer',
            'active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Delivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Kilometres between this band's anchor and the given point.
     */
    public function distanceTo(float $latitude, float $longitude): float
    {
        return GeoDistance::kilometres(
            $this->origin_latitude,
            $this->origin_longitude,
            $latitude,
            $longitude,
        );
    }

    /**
     * A band without a maximum distance is the catch-all outer band.
     */
    public function covers(float $distanceKm): bool
    {
        return $this->max_distance_km === null || $distanceKm <= $this->max_distance_km;
    }

    /**
     * @param  Builder<$this>  $builder
     */
    public function scopeActive(Builder $builder): void
    {
        $builder->where('active', true);
    }
}
