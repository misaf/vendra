<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Translation\Translator;
use Misaf\VendraLanguage\Localization\NamespacedTranslationLoaderManager;
use Misaf\VendraLanguage\Localization\TranslationLoaders\DatabaseTranslationLoader;
use Misaf\VendraLanguage\Models\LanguageLine;
use Misaf\VendraLanguage\Providers\LanguageServiceProvider;
use Misaf\VendraLanguage\Tests\Fixtures\ArrayTranslationLoader;
use Misaf\VendraLanguage\Tests\Fixtures\CustomLanguageLine;
use Misaf\VendraLanguage\Tests\Fixtures\CustomNamespacedLanguageLine;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\TenantSchema;
use Spatie\TranslationLoader\TranslationLoaders\Db;

use function Pest\Laravel\mock;

afterEach(function (): void {
    ArrayTranslationLoader::$translations = [];
});

beforeEach(function (): void {
    $this->currentTenantId = 1;
    $tenantResolver = mock(TenantResolver::class);

    $tenantResolver->shouldReceive('available')->andReturnTrue();

    $tenantResolver->shouldReceive('foreignKey')->andReturn(TenantSchema::DEFAULT_FOREIGN_KEY);

    $tenantResolver->shouldReceive('current')->andReturnNull();
    $tenantResolver->shouldReceive('currentId')->andReturnUsing(
        fn (): int => $this->currentTenantId,
    );

    app()->instance(TenantResolver::class, $tenantResolver);
});

it('allows the same translation key in different groups but rejects exact duplicates', function (): void {
    LanguageLine::query()->create([
        'group' => 'authentication',
        'key' => 'email',
        'text' => ['en' => 'Email'],
    ]);

    LanguageLine::query()->create([
        'group' => 'validation',
        'key' => 'email',
        'text' => ['en' => 'The email field is required.'],
    ]);

    expect(fn (): LanguageLine => LanguageLine::query()->create([
        'group' => 'authentication',
        'key' => 'email',
        'text' => ['en' => 'Email address'],
    ]))->toThrow(QueryException::class);
});

it('scopes translation uniqueness by namespace', function (): void {
    LanguageLine::query()->create([
        'namespace' => 'vendra-product',
        'group' => 'attributes',
        'key' => 'name',
        'text' => ['en' => 'Product name'],
    ]);

    LanguageLine::query()->create([
        'namespace' => 'vendra-language',
        'group' => 'attributes',
        'key' => 'name',
        'text' => ['en' => 'Language name'],
    ]);

    expect(fn (): LanguageLine => LanguageLine::query()->create([
        'namespace' => 'vendra-product',
        'group' => 'attributes',
        'key' => 'name',
        'text' => ['en' => 'Duplicate product name'],
    ]))->toThrow(QueryException::class);
});

it('still rejects duplicate application translations with a null namespace', function (): void {
    LanguageLine::query()->create([
        'namespace' => null,
        'group' => 'validation',
        'key' => 'required',
        'text' => ['en' => 'Required'],
    ]);

    expect(fn (): LanguageLine => LanguageLine::query()->create([
        'namespace' => null,
        'group' => 'validation',
        'key' => 'required',
        'text' => ['en' => 'This field is required'],
    ]))->toThrow(QueryException::class);
});

it('invalidates cache entries for the original group and removed locales', function (): void {
    $languageLine = LanguageLine::query()->create([
        'group' => 'navigation',
        'key' => 'dashboard',
        'text' => [
            'en' => 'Dashboard',
            'de' => 'Instrumententafel',
        ],
    ]);

    Cache::put(LanguageLine::getCacheKey('navigation', 'en'), ['dashboard' => 'Dashboard']);
    Cache::put(LanguageLine::getCacheKey('navigation', 'de'), ['dashboard' => 'Instrumententafel']);

    $languageLine->update([
        'group' => 'modules',
        'text' => ['en' => 'Dashboard'],
    ]);

    expect(Cache::has(LanguageLine::getCacheKey('navigation', 'en')))->toBeFalse()
        ->and(Cache::has(LanguageLine::getCacheKey('navigation', 'de')))->toBeFalse();
});

it('uses tenant-qualified translation cache keys', function (): void {
    $tenantOneKey = LanguageLine::getCacheKey('navigation', 'en');

    $this->currentTenantId = 2;

    expect(LanguageLine::getCacheKey('navigation', 'en'))
        ->not->toBe($tenantOneKey);
});

it('uses namespace-qualified translation cache keys', function (): void {
    expect(LanguageLine::getCacheKey('attributes', 'en', 'vendra-product'))
        ->not->toBe(LanguageLine::getCacheKey('attributes', 'en', 'vendra-language'))
        ->and(LanguageLine::getCacheKey('attributes', 'en', 'vendra-product'))
        ->not->toBe(LanguageLine::getCacheKey('attributes', 'en'));
});

it('invalidates the original namespace cache when a line moves', function (): void {
    $languageLine = LanguageLine::query()->create([
        'namespace' => 'vendra-product',
        'group' => 'attributes',
        'key' => 'name',
        'text' => ['en' => 'Product name'],
    ]);

    $cacheKey = LanguageLine::getCacheKey('attributes', 'en', 'vendra-product');
    Cache::put($cacheKey, ['name' => 'Product name']);

    $languageLine->update(['namespace' => 'vendra-language']);

    expect(Cache::has($cacheKey))->toBeFalse();
});

it('overrides package translations from the database without losing file translations', function (): void {
    LanguageLine::query()->create([
        'namespace' => 'vendra-language',
        'group' => 'navigation',
        'key' => 'language',
        'text' => ['en' => 'Tenant Languages'],
    ]);

    $translator = app('translator');

    expect($translator)->toBeInstanceOf(Translator::class);

    $translator->setLocale('en');
    $translator->setLoaded([]);

    expect(__('vendra-language::navigation.language'))->toBe('Tenant Languages')
        ->and(__('vendra-language::navigation.languages'))->toBe('Languages');
});

it('keeps file translations when the requested database locale is missing or blank', function (): void {
    LanguageLine::query()->create([
        'namespace' => 'vendra-language',
        'group' => 'navigation',
        'key' => 'language',
        'text' => [
            'en' => 'Tenant Languages',
            'de' => '  ',
        ],
    ]);

    $translator = app('translator');
    $translator->setLocale('de');
    $translator->setLoaded([]);

    expect(__('vendra-language::navigation.language'))->toBe('Sprache');
});

it('loads application translations with a null namespace from the database', function (): void {
    LanguageLine::query()->create([
        'group' => 'messages',
        'key' => 'welcome',
        'text' => ['en' => 'Welcome!'],
    ]);

    $translator = app('translator');
    $translator->setLocale('en');
    $translator->setLoaded([]);

    expect(__('messages.welcome'))->toBe('Welcome!');
});

it('applies additional configured translation loaders to namespaced groups', function (): void {
    ArrayTranslationLoader::$translations = [
        'vendra-language::navigation' => ['language' => 'Custom Loader Language'],
    ];

    config([
        'translation-loader.translation_loaders' => [
            ...config('translation-loader.translation_loaders'),
            ArrayTranslationLoader::class,
        ],
    ]);

    $translator = app('translator');
    $translator->setLocale('en');
    $translator->setLoaded([]);

    expect(__('vendra-language::navigation.language'))->toBe('Custom Loader Language');
});

it('registers the namespaced database translation loader and language line model', function (): void {
    expect(config('translation-loader.translation_loaders'))->toContain(DatabaseTranslationLoader::class)
        ->and(in_array(Db::class, config('translation-loader.translation_loaders'), true))->toBeFalse()
        ->and(config('translation-loader.model'))->toBe(LanguageLine::class)
        ->and(config('translation-loader.translation_manager'))->toBe(NamespacedTranslationLoaderManager::class)
        ->and(app('translation.loader'))->toBeInstanceOf(NamespacedTranslationLoaderManager::class);
});

it('upgrades the stock Db loader while preserving host translation loader overrides', function (): void {
    config([
        'translation-loader.translation_loaders' => [Db::class, ArrayTranslationLoader::class],
        'translation-loader.model' => 'App\Models\CustomLanguageLine',
        'translation-loader.translation_manager' => 'App\Localization\CustomTranslationManager',
    ]);

    app()->getProvider(LanguageServiceProvider::class)->packageRegistered();

    expect(config('translation-loader.translation_loaders'))
        ->toBe([DatabaseTranslationLoader::class, ArrayTranslationLoader::class])
        ->and(config('translation-loader.model'))->toBe('App\Models\CustomLanguageLine')
        ->and(config('translation-loader.translation_manager'))->toBe('App\Localization\CustomTranslationManager');
});

it('uses a host model for application translations without sending unsupported namespaces', function (): void {
    config(['translation-loader.model' => CustomLanguageLine::class]);

    $loader = new DatabaseTranslationLoader;

    expect($loader->loadTranslations('en', 'messages'))->toBe(['custom' => 'en:messages'])
        ->and($loader->loadTranslations('en', 'navigation', 'vendra-language'))->toBe([]);
});

it('sends namespaces to host models that implement the namespace contract', function (): void {
    config(['translation-loader.model' => CustomNamespacedLanguageLine::class]);

    expect((new DatabaseTranslationLoader)->loadTranslations('en', 'navigation', 'vendra-language'))
        ->toBe(['custom' => 'vendra-language:en:navigation']);
});
