<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraCurrency\Actions\SetDefaultCurrencyAction;
use Misaf\VendraCurrency\Database\Factories\CurrencyFactory;
use Misaf\VendraCurrency\Models\Currency;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\NullTenantResolver;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
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

it('makes the first active currency the default', function (): void {
    $currency = CurrencyFactory::new()->code('USD')->createOne(['position' => 1]);

    expect($currency->is_default)->toBeTrue();
});

it('does not allow the only default currency to be unset', function (): void {
    $currency = CurrencyFactory::new()->code('USD')->default()->createOne(['position' => 1]);

    $currency->update(['is_default' => false]);

    expect($currency->refresh()->is_default)->toBeTrue();
});

it('promotes the first ordered currency when the default is deleted', function (): void {
    $default = CurrencyFactory::new()->code('USD')->default()->createOne(['position' => 2]);
    $next = CurrencyFactory::new()->code('EUR')->createOne(['position' => 1]);

    $default->delete();

    expect($next->refresh()->is_default)->toBeTrue();
});

it('promotes the first active currency when the default is inactive', function (): void {
    $default = CurrencyFactory::new()->code('USD')->default()->createOne(['position' => 2]);
    $next = CurrencyFactory::new()->code('EUR')->createOne(['position' => 1]);

    $default->update(['active' => false]);

    expect($default->refresh()->active)->toBeFalse()
        ->and($default->is_default)->toBeFalse()
        ->and($next->refresh()->is_default)->toBeTrue();
});

it('disables the default flag when creating a disabled currency', function (): void {
    $currency = CurrencyFactory::new()->code('USD')->inactive()->default()->createOne(['position' => 1]);

    expect($currency->refresh()->is_default)->toBeFalse();
});

it('switches the default currency through the domain action', function (): void {
    $dollar = CurrencyFactory::new()->code('USD')->default()->createOne(['position' => 1]);
    $euro = CurrencyFactory::new()->code('EUR')->createOne(['position' => 2]);

    (new SetDefaultCurrencyAction)->execute($euro);

    expect($dollar->refresh()->is_default)->toBeFalse()
        ->and($euro->refresh()->is_default)->toBeTrue();
});

it('enables a disabled currency when it becomes the default', function (): void {
    CurrencyFactory::new()->code('USD')->default()->createOne(['position' => 1]);
    $euro = CurrencyFactory::new()->code('EUR')->inactive()->createOne(['position' => 2]);

    (new SetDefaultCurrencyAction)->execute($euro);

    expect($euro->refresh()->active)->toBeTrue()
        ->and($euro->is_default)->toBeTrue();
});

it('rejects bulk updates that would create multiple default currencies', function (): void {
    CurrencyFactory::new()->code('USD')->createOne(['is_default' => false, 'position' => 1]);
    CurrencyFactory::new()->code('EUR')->createOne(['is_default' => false, 'position' => 2]);

    expect(fn (): int => Currency::query()->update(['is_default' => true]))
        ->toThrow(QueryException::class);
});

it('creates the fresh currency schema expected by the model', function (): void {
    $columns = [
        'id',
        'code',
        'name',
        'symbol',
        'decimal_places',
        'type',
        'active',
        'is_default',
        'default_guard',
        'position',
        'created_at',
        'updated_at',
    ];

    if (TenantAwareness::enabled()) {
        $columns[] = 'tenant_id';
    }

    expect(Schema::hasColumns('currencies', $columns))->toBeTrue()
        ->and(Schema::hasColumn('currencies', 'iso_code'))->toBeFalse()
        ->and(Schema::hasColumn('currencies', 'deleted_at'))->toBeFalse()
        ->and(Schema::hasTable('currency_categories'))->toBeFalse();
});

it('creates the currencies table without a tenant column when tenancy is unavailable', function (): void {
    app()->instance(TenantResolver::class, new NullTenantResolver);

    Schema::dropIfExists('currencies');

    $migration = require __DIR__.'/../../database/migrations/create_currencies_table.php.stub';

    $migration->up();

    expect(Schema::hasColumn('currencies', 'tenant_id'))->toBeFalse()
        ->and(Schema::hasColumns('currencies', ['code', 'name', 'decimal_places', 'type', 'default_guard']))->toBeTrue();
});
