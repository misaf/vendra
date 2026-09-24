<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Models;

use Cknow\Money\Money;
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
use Illuminate\Support\Str;
use LogicException;
use Misaf\VendraCurrency\Database\Factories\CurrencyFactory;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Observers\CurrencyObserver;
use Misaf\VendraSupport\Contracts\ShouldLogActivity;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Money\Currencies\CurrencyList;
use Money\Currency as MoneyCurrency;
use Money\Formatter\DecimalMoneyFormatter;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

/**
 * Exactly one active currency is the default.
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
     * Pin the sortable behavior regardless of the global config.
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
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    #[Scope]
    protected function active(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function money(int|string $minorUnits): Money
    {
        return new Money($minorUnits, $this->moneyCurrency());
    }

    /**
     * Format minor units for display, such as `$10.00` or `0.00150000 BTC`.
     */
    public function formatAmount(int|string $minorUnits): string
    {
        $money = $this->money($minorUnits);

        if ($this->type === CurrencyType::Fiat) {
            return $money->format();
        }

        $currency = $this->moneyCurrency();
        $formatter = new DecimalMoneyFormatter(new CurrencyList([$currency->getCode() => max(0, $this->decimal_places)]));

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

    private function moneyCurrency(): MoneyCurrency
    {
        throw_if($this->code === '', LogicException::class, 'A currency must have a code.');

        return new MoneyCurrency($this->code);
    }
}
