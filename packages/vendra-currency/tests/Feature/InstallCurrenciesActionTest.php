<?php

declare(strict_types=1);

use Illuminate\Validation\ValidationException;
use Misaf\VendraCurrency\Actions\InstallCurrenciesAction;
use Misaf\VendraCurrency\Database\Factories\CurrencyFactory;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantSchema;

use function Pest\Laravel\mock;

beforeEach(function (): void {
    $tenantResolver = mock(TenantResolver::class);

    $tenantResolver->shouldReceive('available')->andReturnTrue();

    $tenantResolver->shouldReceive('foreignKey')->andReturn(TenantSchema::DEFAULT_FOREIGN_KEY);

    $tenantResolver->shouldReceive('current')->andReturnNull();
    $tenantResolver->shouldReceive('currentId')->andReturn(1);

    app()->instance(TenantResolver::class, $tenantResolver);
});

it('installs catalog currencies and snapshots their data', function (): void {
    $installed = resolve(InstallCurrenciesAction::class)->execute(['EUR', 'BTC']);

    $euro = Currency::query()->where('code', 'EUR')->firstOrFail();
    $bitcoin = Currency::query()->where('code', 'BTC')->firstOrFail();

    expect($installed)->toBe(2)
        ->and($euro->name)->toBe('Euro')
        ->and($euro->decimal_places)->toBe(2)
        ->and($euro->type)->toBe(CurrencyType::Fiat)
        ->and($bitcoin->decimal_places)->toBe(8)
        ->and($bitcoin->type)->toBe(CurrencyType::Crypto);
});

it('skips unsupported and already installed codes', function (): void {
    CurrencyFactory::new()->code('USD')->createOne(['position' => 1]);

    $installed = resolve(InstallCurrenciesAction::class)->execute(['USD', 'usd', 'NOPE', 'EUR']);

    expect($installed)->toBe(1)
        ->and(Currency::query()->where('code', 'USD')->count())->toBe(1)
        ->and(Currency::query()->where('code', 'EUR')->count())->toBe(1)
        ->and(Currency::query()->where('code', 'NOPE')->count())->toBe(0);
});

it('refuses an empty code list', function (): void {
    expect(fn (): int => resolve(InstallCurrenciesAction::class)->execute([]))
        ->toThrow(ValidationException::class);
});
