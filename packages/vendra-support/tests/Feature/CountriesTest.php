<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Misaf\VendraSupport\Capabilities\Countries;

it('provides official ISO countries with localized names', function (): void {
    $englishCountries = Countries::options('en');
    $persianCountries = Countries::options('fa');

    expect($englishCountries)
        ->toHaveCount(249)
        ->not->toHaveKeys(['EU', 'UN', 'XK'])
        ->and(Countries::codes())->toHaveCount(249)->toContain('IR')
        ->and(Arr::get($englishCountries, 'IR'))->toBe('Iran')
        ->and(Arr::get($persianCountries, 'IR'))->toBe('ایران');
});

it('uses the application locale by default', function (): void {
    app()->setLocale('fa');

    expect(Arr::get(Countries::options(), 'IR'))->toBe('ایران')
        ->and(Countries::name('IR'))->toBe('ایران');
});
