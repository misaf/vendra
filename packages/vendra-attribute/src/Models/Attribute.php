<?php

declare(strict_types=1);

namespace Misaf\VendraAttribute\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Misaf\VendraAttribute\Database\Factories\AttributeFactory;
use Misaf\VendraSupport\Capabilities\HasOptionalTags;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * @property int $id
 * @property int $tenant_id
 * @property string $name
 * @property string|null $description
 * @property string|null $unit
 * @property int $position
 * @property bool $active
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable(['name', 'description', 'unit', 'position', 'active'])]
#[Hidden(['tenant_id', 'active_name_guard'])]
#[UseFactory(AttributeFactory::class)]
final class Attribute extends Model implements ShouldLogActivity, Sortable
{
    use BelongsToTenant;

    /** @use HasFactory<AttributeFactory> */
    use HasFactory;

    use HasOptionalTags;
    use SoftDeletes;
    use SortableTrait;

    public const string TAG_TYPE = 'attribute';

    /** @var array{order_column_name: string, sort_when_creating: bool} */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /** @return HasMany<AttributeValue, $this> */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'name' => 'string',
            'description' => 'string',
            'unit' => 'string',
            'position' => 'integer',
            'active' => 'boolean',
        ];
    }

    protected function tagType(): string
    {
        return self::TAG_TYPE;
    }
}
