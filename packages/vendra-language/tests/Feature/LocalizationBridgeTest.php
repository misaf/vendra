<?php

declare(strict_types=1);

use Misaf\VendraLanguage\Localization\LanguageSwitchLocaleResolver;
use Misaf\VendraLanguage\Localization\TenantLocaleResolver;
use Misaf\VendraLanguage\Support\Locales;
use Misaf\VendraLocalization\Contracts\LocaleResolver;
use Misaf\VendraLocalization\Resolvers\QueryLocaleResolver;

beforeEach(function (): void {
    if (! interface_exists(LocaleResolver::class)) {
        $this->markTestSkipped('The optional misaf/vendra-localization package is not installed.');
    }
});

it('feeds the localization supported locales from the language catalog', function (): void {
    expect(config('vendra-localization.supported_locales'))
        ->toBe(Locales::all());
});

it('appends the tenant locale resolver as the lowest-priority chain link', function (): void {
    $resolvers = config('vendra-localization.resolvers');

    expect($resolvers)
        ->toContain(TenantLocaleResolver::class)
        ->and(array_key_last($resolvers))->toBe(array_search(TenantLocaleResolver::class, $resolvers, true));
});

it('places the language switch preference immediately after the query resolver', function (): void {
    $resolvers = config()->array('vendra-localization.resolvers');
    $queryResolver = array_search(QueryLocaleResolver::class, $resolvers, true);
    $switchResolver = array_search(LanguageSwitchLocaleResolver::class, $resolvers, true);

    expect($queryResolver)->toBeInt()
        ->and($switchResolver)->toBe($queryResolver + 1);
});
