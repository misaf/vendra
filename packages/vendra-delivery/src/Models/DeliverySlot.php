<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraDelivery\Database\Factories\DeliverySlotFactory;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Translatable\HasTranslations;

/**
 * A window of the day a customer can be delivered in, such as "Morning 9–12".
 *
 * @property int $id
 * @property int $tenant_id
 * @property array<string, string> $name
 * @property string $starts_at
 * @property string $ends_at
 * @property int|null $capacity
 * @property int $position
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'starts_at', 'ends_at', 'capacity', 'position', 'active'])]
#[Hidden(['tenant_id'])]
#[UseFactory(DeliverySlotFactory::class)]
final class DeliverySlot extends Model implements ShouldLogActivity, Sortable
{
    use BelongsToTenant;

    /** @use HasFactory<DeliverySlotFactory> */
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
    public array $translatable = ['name'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'name' => 'array',
            'starts_at' => 'string',
            'ends_at' => 'string',
            'capacity' => 'integer',
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
     * Whether the slot still has room on the given date. A slot without a
     * capacity is unlimited.
     */
    public function hasRoomOn(string $date): bool
    {
        if ($this->capacity === null) {
            return true;
        }

        return $this->deliveries()->whereDate('scheduled_for', $date)->count() < $this->capacity;
    }

    /**
     * @param  Builder<$this>  $builder
     */
    public function scopeActive(Builder $builder): void
    {
        $builder->where('active', true);
    }
}
