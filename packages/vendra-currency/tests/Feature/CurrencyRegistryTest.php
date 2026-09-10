<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Support\CurrencyRegistry;

it('exposes ISO fiat currencies with names and minor units', function (): void {
    $usd = CurrencyRegistry::get('USD');

    expect($usd)->not->toBeNull()
        ->and(Arr::get($usd, 'name'))->toBe('US Dollar')
        ->and(Arr::get($usd, 'minor_unit'))->toBe(2)
        ->and(Arr::get($usd, 'type'))->toBe(CurrencyType::Fiat);
});

it('exposes crypto currencies with their code as the name', function (): void {
    $bitcoin = CurrencyRegistry::get('BTC');

    expect($bitcoin)->not->toBeNull()
        ->and(Arr::get($bitcoin, 'name'))->toBe('BTC')
        ->and(Arr::get($bitcoin, 'minor_unit'))->toBe(8)
        ->and(Arr::get($bitcoin, 'type'))->toBe(CurrencyType::Crypto);
});

it('builds select options labelled with name and code', function (): void {
    $options = CurrencyRegistry::options();

    expect(Arr::get($options, 'USD'))->toBe('US Dollar (USD)')
        ->and(Arr::get($options, 'BTC'))->toBe('BTC')
        ->and(count($options))->toBeGreaterThan(300);
});

it('orders options by display name ascending', function (): void {
    $labels = array_values(CurrencyRegistry::options());
    $sortedLabels = $labels;
    sort($sortedLabels);

    expect($labels)->toBe($sortedLabels);
});

it('narrows options to a single currency type', function (): void {
    expect(CurrencyRegistry::options(CurrencyType::Fiat))
        ->toHaveKey('USD')
        ->not->toHaveKey('BTC')
        ->and(CurrencyRegistry::options(CurrencyType::Crypto))
        ->toHaveKey('BTC')
        ->not->toHaveKey('USD');
});

it('matches codes case-insensitively', function (): void {
    expect(CurrencyRegistry::isSupported('usd'))->toBeTrue()
        ->and(CurrencyRegistry::isSupported('btc'))->toBeTrue()
        ->and(CurrencyRegistry::isSupported('NOT-A-CODE'))->toBeFalse();
});

it('falls back gracefully for unknown codes', function (): void {
    expect(CurrencyRegistry::get('XXAB'))->toBeNull()
        ->and(CurrencyRegistry::nameFor('xxab'))->toBe('XXAB')
        ->and(CurrencyRegistry::minorUnitFor('XXAB'))->toBeNull()
        ->and(CurrencyRegistry::typeFor('XXAB'))->toBeNull();
});
