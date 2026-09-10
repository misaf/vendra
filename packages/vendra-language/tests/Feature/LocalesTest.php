<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Misaf\VendraLanguage\Support\Locales;

it('lists the module shipped locales as supported web tags', function (): void {
    expect(Locales::all())
        ->toContain('en', 'de', 'fa');
});

it('exposes locales in web form within the column length', function (): void {
    foreach (Locales::all() as $locale) {
        expect($locale)
            ->not->toContain('_')
            ->and(mb_strlen($locale))->toBeLessThanOrEqual(8);
    }
});

it('recognizes supported locales including region variants', function (): void {
    expect(Locales::isSupported('en'))->toBeTrue()
        ->and(Locales::isSupported('pt-BR'))->toBeTrue()
        ->and(Locales::isSupported('not-a-locale'))->toBeFalse()
        ->and(Locales::isSupported(''))->toBeFalse();
});

it('normalizes casing and separators to the web form', function (): void {
    expect(Locales::normalize('pt_br'))->toBe('pt-BR')
        ->and(Locales::normalize('EN'))->toBe('en')
        ->and(Locales::normalize('garbage__'))->toBeNull();
});

it('builds select options keyed by tag with a localized label', function (): void {
    $options = Locales::options('en');

    expect($options)->toHaveKey('de')
        ->and(Arr::get($options, 'de'))->toBe('German (de)');
});

it('labels a given set of locales for a select', function (): void {
    expect(Locales::names(['en', 'de'], 'en'))
        ->toBe(['en' => 'English (en)', 'de' => 'German (de)']);
});

it('canonicalizes the configured catalog and excludes unsupported locales', function (): void {
    config()->set('vendra-language.locales', ['EN', 'pt_br', 'garbage__', 'pt-BR', 123]);

    expect(Locales::configured())->toBe(['en', 'pt-BR']);
});

it('falls back to the application fallback locale when the configured catalog is empty', function (): void {
    config()->set('app.locale', 'fa');
    config()->set('app.fallback_locale', 'DE');
    config()->set('vendra-language.locales', []);

    expect(Locales::configured())->toBe(['de']);
});

it('builds empty translation values from the configured catalog', function (): void {
    config()->set('vendra-language.locales', ['en', 'de', 'fa']);

    expect(Locales::translationDefaults())->toBe([
        'en' => '',
        'de' => '',
        'fa' => '',
    ]);
});
