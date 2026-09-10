<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraAttribute\Database\Factories\AttributeValueFactory;
use Misaf\VendraAttribute\Observers\AttributeValueObserver;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $attribute_id
 * @property string $attributable_type
 * @property int $attributable_id
 * @property string $value
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['attribute_id', 'value', 'position'])]
#[Hidden(['tenant_id', 'attributable_type', 'attributable_id', 'active_value_guard'])]
#[ObservedBy([AttributeValueObserver::class])]
#[UseFactory(AttributeValueFactory::class)]
final class AttributeValue extends Model implements ShouldLogActivity, Sortable
{
    use BelongsToTenant;

    /** @use HasFactory<AttributeValueFactory> */
    use HasFactory;

    use SoftDeletes;
    use SortableTrait;

    /** @var array{order_column_name: string, sort_when_creating: bool} */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /**
     * The DB-level cascade on attribute_value_selections only fires on hard
     * deletes, so soft deletes must detach selections themselves; restoring
     * a value intentionally does not resurrect them.
     */

    /** @return BelongsTo<Attribute, $this> */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(Attribute::class);
    }

    /** @return MorphTo<Model, $this> */
    public function attributable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The entities that selected this attribute value through the
     * attribute_value_selections pivot.
     *
     * @return HasMany<AttributeValueSelection, $this>
     */
    public function selections(): HasMany
    {
        return $this->hasMany(AttributeValueSelection::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'attribute_id' => 'integer',
            'attributable_type' => 'string',
            'attributable_id' => 'integer',
            'value' => 'string',
            'position' => 'integer',
        ];
    }
}
