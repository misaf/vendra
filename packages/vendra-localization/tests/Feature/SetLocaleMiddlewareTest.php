<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Log\Context\Repository;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Number;
use Misaf\VendraLocalization\Contracts\LocaleResolver;
use Misaf\VendraLocalization\Http\Middleware\SetLocale;
use Misaf\VendraLocalization\LocaleManager;
use Misaf\VendraLocalization\Resolvers\ChainLocaleResolver;
use Misaf\VendraLocalization\Tests\Fixtures\TenantLocaleResolver;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    config(['vendra-localization.supported_locales' => ['en', 'de', 'fa', 'fr']]);
});

function handleSetLocale(Request $request, string ...$sources): Response
{
    return resolve(SetLocale::class)->handle(
        $request,
        fn (Request $request): Response => new Response('ok'),
        ...$sources,
    );
}

it('sets the resolved supported locale and Content-Language header', function (?string $resolvedLocale, string $expectedLocale): void {
    app()->instance(LocaleResolver::class, new TenantLocaleResolver($resolvedLocale));

    $response = handleSetLocale(Request::create('/'));

    expect(app()->getLocale())
        ->toBe($expectedLocale)
        ->and($response->headers->get('Content-Language'))
        ->toBe($expectedLocale)
        ->and($response->headers->get('Vary'))
        ->toBeNull();
})->with([
    'supported locale' => ['fa', 'fa'],
    'unsupported locale' => ['es', 'en'],
    'region variant of a supported locale' => ['fr-CA', 'fr'],
    'underscored region variant' => ['fr_CA', 'fr'],
    'case-insensitive region variant' => ['FR_ca', 'fr'],
    'no resolved locale' => [null, 'en'],
]);

it('uses the application fallback locale when resolution fails', function (): void {
    config()->set('app.locale', 'de');
    config()->set('app.fallback_locale', 'fa');

    app()->instance(LocaleResolver::class, new TenantLocaleResolver(null));

    $response = handleSetLocale(Request::create('/'));

    expect(app()->getLocale())
        ->toBe('fa')
        ->and($response->headers->get('Content-Language'))
        ->toBe('fa');
});

it('matches supported locales after normalizing region separators', function (): void {
    config()->set('vendra-localization.supported_locales', ['en', 'fr-CA']);

    app()->instance(LocaleResolver::class, new TenantLocaleResolver('fr_CA'));

    $response = handleSetLocale(Request::create('/'));

    expect(app()->getLocale())
        ->toBe('fr-CA')
        ->and($response->headers->get('Content-Language'))
        ->toBe('fr-CA');
});

it('adds Vary headers declared by the resolver chain', function (): void {
    app()->instance(LocaleResolver::class, ChainLocaleResolver::fromSources(app(), ['accept-language']));

    $response = handleSetLocale(Request::create('/', server: [
        'HTTP_ACCEPT_LANGUAGE' => 'fa',
    ]));

    expect(app()->getLocale())
        ->toBe('fa')
        ->and($response->headers->get('Vary'))
        ->toBe('Accept-Language');
});

it('does not add an empty Vary header', function (): void {
    app()->instance(LocaleResolver::class, new ChainLocaleResolver(new TenantLocaleResolver('fa')));

    $response = handleSetLocale(Request::create('/'));

    expect($response->headers->get('Vary'))->toBeNull();
});

it('builds the resolver chain from middleware parameters', function (): void {
    $response = handleSetLocale(Request::create('/?locale=de'), 'query');

    expect(app()->getLocale())
        ->toBe('de')
        ->and($response->headers->get('Vary'))
        ->toBeNull();
});

it('registers the vendra.locale middleware alias', function (): void {
    expect(resolve(Router::class)->getMiddleware())->toHaveKey('vendra.locale', SetLocale::class);
});

it('syncs the Carbon and Number locales when enabled', function (): void {
    config()->set('vendra-localization.sync', ['carbon' => true, 'number' => true]);

    app()->instance(LocaleResolver::class, new TenantLocaleResolver('fa'));

    handleSetLocale(Request::create('/'));

    expect(Date::getLocale())
        ->toBe('fa')
        ->and(Number::defaultLocale())
        ->toBe('fa');
});

it('propagates the locale to queued execution as hidden context', function (): void {
    config()->set('vendra-localization.sync', ['carbon' => true, 'number' => true]);
    app()->instance(LocaleResolver::class, new TenantLocaleResolver('fa'));

    handleSetLocale(Request::create('/'));

    $repository = resolve(Repository::class);
    $dehydrated = $repository->dehydrate();

    $repository->flush();
    resolve(LocaleManager::class)->apply('en');
    $repository->hydrate($dehydrated);

    expect(app()->getLocale())->toBe('fa')
        ->and(Date::getLocale())->toBe('fa')
        ->and(Number::defaultLocale())->toBe('fa')
        ->and(Context::get(LocaleManager::CONTEXT_KEY))->toBeNull()
        ->and(Context::getHidden(LocaleManager::CONTEXT_KEY))->toBe('fa');
});
