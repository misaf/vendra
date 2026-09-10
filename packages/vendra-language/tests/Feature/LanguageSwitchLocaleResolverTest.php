<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Misaf\VendraLanguage\Localization\LanguageSwitchLocaleResolver;
use Misaf\VendraLanguage\Models\Language;
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

it('resolves the locale selected in the language switch session', function (): void {
    Language::query()->create(['locale' => 'de', 'position' => 1]);

    $request = Request::create('/');
    $request->setLaravelSession(app('session')->driver());
    $request->session()->put('locale', 'de');

    expect((new LanguageSwitchLocaleResolver)->resolve($request))->toBe('de');
});

it('falls back to the language switch cookie', function (): void {
    Language::query()->create(['locale' => 'fa', 'position' => 1]);

    $request = Request::create('/', cookies: [
        'filament_language_switch_locale' => 'fa',
    ]);

    expect((new LanguageSwitchLocaleResolver)->resolve($request))->toBe('fa');
});

it('ignores a language switch preference for an inactive language', function (): void {
    Language::query()->create([
        'locale' => 'de',
        'active' => false,
        'position' => 1,
    ]);

    $request = Request::create('/');
    $request->setLaravelSession(app('session')->driver());
    $request->session()->put('locale', 'de');

    expect((new LanguageSwitchLocaleResolver)->resolve($request))->toBeNull();
});
