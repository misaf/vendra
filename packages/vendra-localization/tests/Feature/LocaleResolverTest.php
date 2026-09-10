<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Misaf\VendraLocalization\Contracts\LocaleResolver;
use Misaf\VendraLocalization\Resolvers\AcceptLanguageLocaleResolver;
use Misaf\VendraLocalization\Resolvers\ChainLocaleResolver;
use Misaf\VendraLocalization\Resolvers\QueryLocaleResolver;
use Misaf\VendraLocalization\Resolvers\RouteLocaleResolver;
use Misaf\VendraLocalization\Resolvers\UserLocaleResolver;
use Misaf\VendraLocalization\Tests\Fixtures\TenantLocaleResolver;

beforeEach(function (): void {
    config(['vendra-localization.supported_locales' => ['en', 'de', 'fa', 'fr']]);
});

function requestWithRouteLocale(?string $locale): Request
{
    $request = Request::create($locale === null ? '/api/products' : "/api/{$locale}/products");
    $route = new Route('GET', $locale === null ? '/api/products' : '/api/{locale}/products', []);
    $route->bind($request);
    $request->setRouteResolver(fn (): Route => $route);

    return $request;
}

function requestWithUserLocale(?string $locale): Request
{
    $request = Request::create('/');

    if ($locale !== null) {
        $request->setUserResolver(fn (): object => (object) ['locale' => $locale]);
    }

    return $request;
}

function requestWithPreferredLocaleUser(string $locale): Request
{
    $request = Request::create('/');

    $request->setUserResolver(fn (): HasLocalePreference => new readonly class($locale) implements HasLocalePreference
    {
        public function __construct(
            private string $locale,
        ) {}

        public function preferredLocale(): string
        {
            return $this->locale;
        }
    });

    return $request;
}

it('binds the configured resolver chain', function (): void {
    config()->set('vendra-localization.resolvers', [TenantLocaleResolver::class]);

    app()->forgetInstance(LocaleResolver::class);

    $resolver = resolve(LocaleResolver::class);

    expect($resolver)
        ->toBeInstanceOf(ChainLocaleResolver::class)
        ->and($resolver->resolve(Request::create('/')))
        ->toBe('fa');
});

it('rejects configured resolvers that do not implement the contract', function (): void {
    config()->set('vendra-localization.resolvers', [stdClass::class]);

    app()->forgetInstance(LocaleResolver::class);

    resolve(LocaleResolver::class);
})->throws(InvalidArgumentException::class);

it('registers every built-in resolver', function (string $resolver): void {
    expect(resolve($resolver))->toBeInstanceOf(LocaleResolver::class);
})->with([
    AcceptLanguageLocaleResolver::class,
    QueryLocaleResolver::class,
    RouteLocaleResolver::class,
    UserLocaleResolver::class,
]);

it('resolves locales from each built-in resolver source', function (string $resolver, Request $request, string $expectedLocale): void {
    expect(resolve($resolver)->resolve($request))->toBe($expectedLocale);
})->with([
    'accept language header' => fn (): array => [
        AcceptLanguageLocaleResolver::class,
        Request::create('/', server: [
            'HTTP_ACCEPT_LANGUAGE' => 'fr-CA,fr;q=0.9,de;q=0.8,en;q=0.7',
        ]),
        'fr',
    ],
    'query string' => fn (): array => [
        QueryLocaleResolver::class,
        Request::create('/?locale=de'),
        'de',
    ],
    'route parameter' => fn (): array => [
        RouteLocaleResolver::class,
        requestWithRouteLocale('fa'),
        'fa',
    ],
    'user locale attribute' => fn (): array => [
        UserLocaleResolver::class,
        requestWithUserLocale('de'),
        'de',
    ],
    'user locale preference' => fn (): array => [
        UserLocaleResolver::class,
        requestWithPreferredLocaleUser('fa'),
        'fa',
    ],
]);

it('returns null when the resolver source has no value', function (string $resolver, Request $request): void {
    expect(resolve($resolver)->resolve($request))->toBeNull();
})->with([
    'accept language header' => fn (): array => [
        AcceptLanguageLocaleResolver::class,
        Request::create('/', server: [
            'HTTP_ACCEPT_LANGUAGE' => '',
        ]),
    ],
    'query string' => fn (): array => [
        QueryLocaleResolver::class,
        Request::create('/'),
    ],
    'route parameter' => fn (): array => [
        RouteLocaleResolver::class,
        requestWithRouteLocale(null),
    ],
    'authenticated user' => fn (): array => [
        UserLocaleResolver::class,
        requestWithUserLocale(null),
    ],
]);

it('returns the first non-null locale from the chain', function (): void {
    $chain = new ChainLocaleResolver(
        new TenantLocaleResolver(null),
        new TenantLocaleResolver('de'),
        new TenantLocaleResolver('fa'),
    );

    expect($chain->resolve(Request::create('/')))->toBe('de');
});

it('returns null when no chained resolver produces a locale', function (): void {
    $chain = new ChainLocaleResolver(new TenantLocaleResolver(null));

    expect($chain->resolve(Request::create('/')))->toBeNull();
});

it('builds chains from source aliases and aggregates vary headers', function (): void {
    $chain = ChainLocaleResolver::fromSources(app(), ['query', 'accept-language']);

    expect($chain->resolve(Request::create('/?locale=de')))
        ->toBe('de')
        ->and($chain->varyHeaders())
        ->toBe(['Accept-Language']);
});
