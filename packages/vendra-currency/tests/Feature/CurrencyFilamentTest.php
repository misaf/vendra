<?php

declare(strict_types=1);

use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Select;
use Misaf\VendraCurrency\Database\Factories\CurrencyFactory;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Pages\CreateCurrency;
use Misaf\VendraCurrency\Filament\Clusters\Resources\Currencies\Pages\ListCurrencies;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Filament\Tables\Columns\IsDefaultIconColumn;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    setUpFilamentAdminTestContext();
});

it('offers every uninstalled catalog currency for installation', function (): void {
    CurrencyFactory::new()->code('USD')->createOne(['position' => 1]);

    livewire(CreateCurrency::class)
        ->assertFormFieldExists('code', function (Select $field): bool {
            $options = $field->getOptions();

            return ! isset($options['USD'])
                && isset($options['EUR'], $options['BTC'])

                && count($options) > 300;
        });
});

it('installs a fiat currency and snapshots its catalog data', function (): void {
    livewire(CreateCurrency::class)
        ->fillForm([
            'code' => 'EUR',
            'is_default' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $euro = Currency::query()->where('code', 'EUR')->firstOrFail();

    expect($euro->name)->toBe('Euro')
        ->and($euro->decimal_places)->toBe(2)
        ->and($euro->type)->toBe(CurrencyType::Fiat);
});

it('installs a crypto currency from the catalog', function (): void {
    livewire(CreateCurrency::class)
        ->fillForm([
            'code' => 'BTC',
            'is_default' => false,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $bitcoin = Currency::query()->where('code', 'BTC')->firstOrFail();

    expect($bitcoin->decimal_places)->toBe(8)
        ->and($bitcoin->type)->toBe(CurrencyType::Crypto);
});

it('rejects codes outside the currency catalog', function (): void {
    livewire(CreateCurrency::class)
        ->fillForm([
            'code' => 'NOPE',
            'name' => 'Not a currency',
        ])
        ->call('create')
        ->assertHasFormErrors(['code']);
});

it('installs multiple currencies at once from the catalog action', function (): void {
    CurrencyFactory::new()->code('USD')->createOne(['position' => 1]);

    livewire(ListCurrencies::class)
        ->callAction('installCurrencies', [
            'codes' => ['EUR', 'BTC'],
        ])
        ->assertHasNoActionErrors();

    $euro = Currency::query()->where('code', 'EUR')->firstOrFail();
    $bitcoin = Currency::query()->where('code', 'BTC')->firstOrFail();

    expect($euro->name)->toBe('Euro')
        ->and($euro->type)->toBe(CurrencyType::Fiat)
        ->and($bitcoin->decimal_places)->toBe(8)
        ->and($bitcoin->type)->toBe(CurrencyType::Crypto);
});

it('requires at least one code in the catalog action', function (): void {
    livewire(ListCurrencies::class)
        ->callAction('installCurrencies', [
            'codes' => [],
        ])
        ->assertHasActionErrors(['codes' => 'required']);

    expect(Currency::query()->count())->toBe(0);
});

it('rejects already installed currencies in the catalog action', function (): void {
    CurrencyFactory::new()->code('USD')->createOne(['position' => 1]);

    livewire(ListCurrencies::class)
        ->callAction('installCurrencies', [
            'codes' => ['USD'],
        ])
        ->assertHasActionErrors(['codes.0']);

    expect(Currency::query()->where('code', 'USD')->count())->toBe(1);
});

it('temporarily disables an installed currency from the table', function (): void {
    $currency = CurrencyFactory::new()->code('USD')->createOne(['position' => 1]);

    livewire(ListCurrencies::class)
        ->call('updateTableColumnState', 'active', (string) $currency->getKey(), false);

    expect($currency->refresh()->active)->toBeFalse();

    livewire(ListCurrencies::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$currency])
        ->assertTableColumnStateSet('active', false, $currency);
});

it('sets a currency as default from the table action', function (): void {
    $dollar = CurrencyFactory::new()->code('USD')->createOne(['position' => 1]);
    $euro = CurrencyFactory::new()->code('EUR')->createOne(['position' => 2]);

    livewire(ListCurrencies::class)
        ->callAction(TestAction::make('setDefault')->table($euro))
        ->assertNotified();

    expect($dollar->refresh()->is_default)->toBeFalse()
        ->and($euro->refresh()->is_default)->toBeTrue();
});

it('shows the default flag as an icon column on the default currency', function (): void {
    $dollar = CurrencyFactory::new()->code('USD')->createOne(['position' => 1]);
    $euro = CurrencyFactory::new()->code('EUR')->createOne(['position' => 2]);

    $euro->update(['is_default' => true]);
    $dollar->update(['is_default' => false]);

    livewire(ListCurrencies::class)
        ->loadTable()
        ->assertTableColumnExists('is_default', fn (IsDefaultIconColumn $column): bool => true, $euro)
        ->assertTableColumnStateSet('is_default', true, $euro)
        ->assertTableColumnStateSet('is_default', false, $dollar);
});
