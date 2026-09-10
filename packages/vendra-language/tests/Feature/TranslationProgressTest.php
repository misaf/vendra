<?php

declare(strict_types=1);

use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraLanguage\Support\TranslationProgress;
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

    Language::query()->create(['locale' => 'en', 'position' => 1]);
    Language::query()->create(['locale' => 'de', 'position' => 2]);

    $this->completeLanguageLine = LanguageLine::query()->create([
        'group' => 'navigation',
        'key' => 'dashboard',
        'text' => [
            'en' => 'Dashboard',
            'de' => 'Instrumententafel',
        ],
    ]);

    $this->partialLanguageLine = LanguageLine::query()->create([
        'group' => 'navigation',
        'key' => 'settings',
        'text' => [
            'en' => '  ',
            'de' => 'Einstellungen',
        ],
    ]);
});

it('calculates override coverage for a locale', function (): void {
    expect(resolve(TranslationProgress::class)->forLocale('en'))->toBe([
        'translated' => 1,
        'total' => 2,
        'remaining' => 1,
        'percentage' => 50,
        'missing_locales' => [],
    ]);
});

it('calculates active locale progress and missing locales for a language line', function (): void {
    expect(resolve(TranslationProgress::class)->forLanguageLine($this->completeLanguageLine))->toBe([
        'translated' => 2,
        'total' => 2,
        'remaining' => 0,
        'percentage' => 100,
        'missing_locales' => [],
    ])->and(resolve(TranslationProgress::class)->forLanguageLine($this->partialLanguageLine))->toBe([
        'translated' => 1,
        'total' => 2,
        'remaining' => 1,
        'percentage' => 50,
        'missing_locales' => ['en'],
    ]);
});
