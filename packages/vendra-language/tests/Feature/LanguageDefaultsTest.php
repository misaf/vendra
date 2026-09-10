<?php

declare(strict_types=1);

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraLanguage\Actions\SetDefaultLanguageAction;
use Misaf\VendraLanguage\Models\Language;
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

it('makes the first active language the default', function (): void {
    $language = Language::query()->create([
        'locale' => 'en',
        'is_default' => false,
        'position' => 1,
    ]);

    expect($language->is_default)->toBeTrue();
});

it('does not allow the only default language to be unset', function (): void {
    $language = Language::query()->create([
        'locale' => 'en',
        'is_default' => true,
        'position' => 1,
    ]);

    $language->update(['is_default' => false]);

    expect($language->refresh()->is_default)->toBeTrue();
});

it('promotes the first ordered language when the default is deleted', function (): void {
    $default = Language::query()->create([
        'locale' => 'en',
        'is_default' => true,
        'position' => 2,
    ]);

    $next = Language::query()->create([
        'locale' => 'de',
        'is_default' => false,
        'position' => 1,
    ]);

    $default->delete();

    expect($next->refresh()->is_default)->toBeTrue();
});

it('promotes the first active language when the default becomes inactive', function (): void {
    $default = Language::query()->create([
        'locale' => 'en',
        'is_default' => true,
        'position' => 2,
    ]);

    $next = Language::query()->create([
        'locale' => 'de',
        'position' => 1,
    ]);

    $default->update(['active' => false]);

    expect($default->refresh()->active)->toBeFalse()
        ->and($default->is_default)->toBeFalse()
        ->and($next->refresh()->is_default)->toBeTrue();
});

it('switches the default language through the domain action', function (): void {
    $english = Language::query()->create([
        'locale' => 'en',
        'is_default' => true,
        'position' => 1,
    ]);

    $german = Language::query()->create([
        'locale' => 'de',
        'is_default' => false,
        'position' => 2,
    ]);

    (new SetDefaultLanguageAction)->execute($german);

    expect($english->refresh()->is_default)->toBeFalse()
        ->and($german->refresh()->is_default)->toBeTrue();
});

it('keeps only one default language for the current tenant', function (): void {
    $currentTenantId = 1;
    $tenantResolver = mock(TenantResolver::class);

    $tenantResolver->shouldReceive('available')->andReturnTrue();

    $tenantResolver->shouldReceive('foreignKey')->andReturn(TenantSchema::DEFAULT_FOREIGN_KEY);

    $tenantResolver->shouldReceive('current')->andReturnNull();
    $tenantResolver->shouldReceive('currentId')->andReturnUsing(function () use (&$currentTenantId): int {
        return $currentTenantId;
    });

    app()->instance(TenantResolver::class, $tenantResolver);

    $english = Language::query()->create([
        'locale' => 'en',
        'is_default' => true,
        'position' => 1,
    ]);

    $german = Language::query()->create([
        'locale' => 'de',
        'is_default' => true,
        'position' => 2,
    ]);

    $currentTenantId = 2;

    $persian = Language::query()->create([
        'locale' => 'fa',
        'is_default' => true,
        'position' => 1,
    ]);

    expect($persian->refresh()->is_default)->toBeTrue()
        ->and(Language::query()->where('is_default', true)->count())->toBe(1);

    $currentTenantId = 1;

    expect($english->refresh()->is_default)->toBeFalse()
        ->and($german->refresh()->is_default)->toBeTrue()
        ->and(Language::query()->where('is_default', true)->count())->toBe(1);
});

it('creates the fresh language schema expected by the model', function (): void {
    $columns = [
        'id',
        'locale',
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

    expect(Schema::hasColumns('languages', $columns))->toBeTrue()
        ->and(Schema::hasColumn('languages', 'iso_code'))->toBeFalse()
        ->and(Schema::hasColumn('languages', 'deleted_at'))->toBeFalse();
});

it('resolves language switch locales after the current tenant is available', function (): void {
    $switch = LanguageSwitch::make();
    $currentTenantId = 1;
    $tenantResolver = mock(TenantResolver::class);

    $tenantResolver->shouldReceive('available')->andReturnTrue();

    $tenantResolver->shouldReceive('foreignKey')->andReturn(TenantSchema::DEFAULT_FOREIGN_KEY);

    $tenantResolver->shouldReceive('current')->andReturnNull();
    $tenantResolver->shouldReceive('currentId')->andReturnUsing(function () use (&$currentTenantId): int {
        return $currentTenantId;
    });

    app()->instance(TenantResolver::class, $tenantResolver);

    Language::query()->create([
        'locale' => 'en',
        'is_default' => true,
        'position' => 1,
    ]);

    Language::query()->create([
        'locale' => 'de',
        'is_default' => false,
        'position' => 2,
    ]);

    $currentTenantId = 2;

    Language::query()->create([
        'locale' => 'fa',
        'is_default' => true,
        'position' => 1,
    ]);

    $currentTenantId = 1;

    expect($switch->getLocales())->toBe(['en', 'de']);
});

it('uses the application fallback locale when no language is active', function (): void {
    config()->set('app.locale', 'de');
    config()->set('app.fallback_locale', 'fa');

    expect(LanguageSwitch::make()->getLocales())->toBe(['fa']);
});

it('rejects bulk updates that would create multiple default languages', function (): void {
    $tenantResolver = mock(TenantResolver::class);

    $tenantResolver->shouldReceive('available')->andReturnTrue();

    $tenantResolver->shouldReceive('foreignKey')->andReturn(TenantSchema::DEFAULT_FOREIGN_KEY);

    $tenantResolver->shouldReceive('current')->andReturnNull();
    $tenantResolver->shouldReceive('currentId')->andReturn(1);

    app()->instance(TenantResolver::class, $tenantResolver);

    Language::query()->create([
        'locale' => 'en',
        'is_default' => false,
        'position' => 1,
    ]);

    Language::query()->create([
        'locale' => 'de',
        'is_default' => false,
        'position' => 2,
    ]);

    expect(fn (): int => Language::query()->update(['is_default' => true]))
        ->toThrow(QueryException::class);
});

it('creates language tables without tenant columns when tenancy is unavailable', function (): void {
    app()->instance(TenantResolver::class, new NullTenantResolver);

    Schema::dropIfExists('language_lines');
    Schema::dropIfExists('languages');

    $migration = require __DIR__.'/../../database/migrations/create_languages_table.php.stub';

    $migration->up();

    expect(Schema::hasColumn('languages', 'tenant_id'))->toBeFalse()
        ->and(Schema::hasColumn('language_lines', 'tenant_id'))->toBeFalse()
        ->and(Schema::hasColumns('language_lines', ['namespace', 'namespace_guard', 'group', 'key', 'text']))->toBeTrue();
});
