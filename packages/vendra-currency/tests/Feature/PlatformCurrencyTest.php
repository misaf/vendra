<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Misaf\VendraCurrency\Actions\InstallCurrenciesAction;
use Misaf\VendraCurrency\Actions\SetDefaultCurrencyAction;
use Misaf\VendraCurrency\Database\Factories\CurrencyFactory;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Contracts\CurrencyResolver;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantSchema;

use function Pest\Laravel\mock;

beforeEach(function (): void {
    $this->currentTenantId = null;

    $tenantResolver = mock(TenantResolver::class);

    $tenantResolver->shouldReceive('available')->andReturnTrue();
    $tenantResolver->shouldReceive('foreignKey')->andReturn(TenantSchema::DEFAULT_FOREIGN_KEY);
    $tenantResolver->shouldReceive('current')->andReturnNull();
    $tenantResolver->shouldReceive('currentId')->andReturnUsing(fn (): ?int => $this->currentTenantId);

    app()->instance(TenantResolver::class, $tenantResolver);
});

function currencyRow(string $code, ?int $tenantId): Currency
{
    return Currency::query()
        ->withoutGlobalScopes()
        ->where('code', $code)
        ->where(TenantSchema::column(), $tenantId)
        ->firstOrFail();
}

it('keeps the platform default apart from each store default', function (): void {
    $this->currentTenantId = 1;
    CurrencyFactory::new()->active()->code('USD')->createOne(['position' => 1]);

    $this->currentTenantId = null;
    CurrencyFactory::new()->active()->code('USD')->createOne(['position' => 1]);
    $euro = CurrencyFactory::new()->active()->code('EUR')->createOne(['position' => 2]);

    resolve(SetDefaultCurrencyAction::class)->execute($euro);

    expect(currencyRow('USD', 1)->is_default)->toBeTrue()
        ->and(currencyRow('USD', null)->is_default)->toBeFalse()
        ->and(currencyRow('EUR', null)->is_default)->toBeTrue();
});

it('installs platform currencies outside a store even when a store has the code', function (): void {
    $this->currentTenantId = 1;
    CurrencyFactory::new()->active()->code('USD')->createOne(['position' => 1]);

    $this->currentTenantId = null;

    expect(resolve(InstallCurrenciesAction::class)->execute(['USD']))->toBe(1)
        ->and(resolve(InstallCurrenciesAction::class)->execute(['USD']))->toBe(0)
        ->and(currencyRow('USD', null)->is_default)->toBeTrue();
});

it('resolves the platform default outside a store and the store default inside one', function (): void {
    $this->currentTenantId = 1;
    CurrencyFactory::new()->active()->code('EUR')->createOne(['position' => 1]);

    $this->currentTenantId = null;
    CurrencyFactory::new()->active()->code('USD')->createOne(['position' => 1]);

    expect(resolve(CurrencyResolver::class)->defaultCode())->toBe('USD')
        ->and(resolve(CurrencyResolver::class)->options())->toBe(['USD' => 'US Dollar'])
        ->and(Currency::query()->platform()->pluck('code')->all())->toBe(['USD']);

    $this->currentTenantId = 1;

    expect(resolve(CurrencyResolver::class)->defaultCode())->toBe('EUR')
        ->and(Currency::query()->platform()->pluck('code')->all())->toBe(['USD']);
});

it('rejects a second platform currency with the same code or a second platform default', function (): void {
    CurrencyFactory::new()->active()->code('USD')->createOne(['position' => 1]);
    CurrencyFactory::new()->active()->code('EUR')->createOne(['position' => 2]);

    expect(fn (): Currency => CurrencyFactory::new()->active()->code('USD')->createOne(['position' => 3]))
        ->toThrow(QueryException::class)
        ->and(fn (): int => Currency::query()->platform()->update(['is_default' => true]))
        ->toThrow(QueryException::class);
});
