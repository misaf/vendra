<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Misaf\VendraLanguage\Database\Factories\LanguageFactory;
use Misaf\VendraLanguage\Observers\LanguageObserver;
use Misaf\VendraLanguage\Support\Locales;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * A locale a tenant has installed from the ICU catalog. Display names derive
 * from that catalog, while active controls whether the locale is active.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $locale
 * @property bool $active
 * @property bool $is_default
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read string $name
 */
#[Fillable(['locale', 'active', 'is_default', 'position'])]
#[Hidden(['tenant_id', 'default_guard'])]
#[ObservedBy([LanguageObserver::class])]
#[UseFactory(LanguageFactory::class)]
final class Language extends Model implements ShouldLogActivity, Sortable
{
    use BelongsToTenant;

    /** @use HasFactory<LanguageFactory> */
    use HasFactory;

    use SortableTrait;

    /**
     * Pin sortable behavior regardless of the global `eloquent-sortable`
     * configuration values: order on the `position` column and always assign
     * the next position when creating.
     *
     * @var array{order_column_name: string, sort_when_creating: bool}
     */
    public array $sortable = [
        'order_column_name' => 'position',
        'sort_when_creating' => true,
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'active' => true,
        'is_default' => false,
    ];

    /**
     * The localized display name resolved from the ICU catalog.
     */
    protected function name(): Attribute
    {
        return Attribute::make(get: fn () => Locales::name($this->locale));
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'locale' => 'string',
            'active' => 'boolean',
            'is_default' => 'boolean',
            'position' => 'integer',
        ];
    }
}
