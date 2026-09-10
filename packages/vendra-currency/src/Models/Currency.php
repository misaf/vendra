<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Models;

use Cknow\Money\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Misaf\VendraCurrency\Database\Factories\CurrencyFactory;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Observers\CurrencyObserver;
use Misaf\VendraCurrency\Support\CurrencyRegistry;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Money\Currencies\CurrencyList;
use Money\Currency as MoneyCurrency;
use Money\Formatter\DecimalMoneyFormatter;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * A currency a tenant has installed from the fiat/crypto catalog exposed by
 * {@see CurrencyRegistry}. Name, symbol, and
 * decimal places are snapshotted at install time and remain editable, while
 * `active` controls availability and exactly one active row is the default.
 *
 * @property int $id
 * @property int $tenant_id
 * @property string $code
 * @property string $name
 * @property string|null $symbol
 * @property int $decimal_places
 * @property CurrencyType $type
 * @property bool $active
 * @property bool $is_default
 * @property int $position
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
#[Fillable(['code', 'name', 'symbol', 'decimal_places', 'type', 'active', 'is_default', 'position'])]
#[Hidden(['tenant_id', 'default_guard'])]
#[ObservedBy([CurrencyObserver::class])]
#[UseFactory(CurrencyFactory::class)]
final class Currency extends Model implements ShouldLogActivity, Sortable
{
    use BelongsToTenant;

    /** @use HasFactory<CurrencyFactory> */
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
     * @param  Builder<Currency>  $query
     * @return Builder<Currency>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * The given amount of minor units (e.g. cents, satoshi) as a money value
     * in this currency.
     */
    public function money(int|string $minorUnits): Money
    {
        return new Money($minorUnits, new MoneyCurrency($this->code));
    }

    /**
     * Format the given amount of minor units for display, e.g. `$10.00` for
     * fiat currencies or `0.00150000 BTC` for crypto currencies unknown to
     * the intl formatter.
     */
    public function formatAmount(int|string $minorUnits): string
    {
        $money = $this->money($minorUnits);

        if ($this->type === CurrencyType::Fiat) {
            return $money->format();
        }

        $formatter = new DecimalMoneyFormatter(new CurrencyList([$this->code => $this->decimal_places]));

        return "{$formatter->format($money->getMoney())} ".($this->symbol ?? $this->code);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'tenant_id' => 'integer',
            'code' => 'string',
            'name' => 'string',
            'symbol' => 'string',
            'decimal_places' => 'integer',
            'type' => CurrencyType::class,
            'active' => 'boolean',
            'is_default' => 'boolean',
            'position' => 'integer',
        ];
    }

    /**
     * @return Attribute<string, string>
     */
    protected function code(): Attribute
    {
        return Attribute::make(
            set: fn (string $value): string => Str::upper($value),
        );
    }
}
