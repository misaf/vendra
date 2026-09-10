<?php

declare(strict_types=1);

use Cknow\Money\Money;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Misaf\VendraCurrency\Models\Currency;

it('wraps minor units in a laravel-money value', function (): void {
    $currency = new Currency([
        'code' => 'USD',
        'name' => 'US Dollar',
        'decimal_places' => 2,
        'type' => CurrencyType::Fiat,
    ]);

    $money = $currency->money(1050);

    expect($money)->toBeInstanceOf(Money::class)
        ->and($money->getAmount())->toBe('1050')
        ->and($money->getCurrency()->getCode())->toBe('USD');
});

it('formats fiat amounts through the intl formatter', function (): void {
    $currency = new Currency([
        'code' => 'USD',
        'name' => 'US Dollar',
        'decimal_places' => 2,
        'type' => CurrencyType::Fiat,
    ]);

    expect($currency->formatAmount(1050))->toBe('$10.50');
});

it('formats crypto amounts using the stored decimal places and code', function (): void {
    $currency = new Currency([
        'code' => 'BTC',
        'name' => 'BTC',
        'decimal_places' => 8,
        'type' => CurrencyType::Crypto,
    ]);

    expect($currency->formatAmount(150000))->toBe('0.00150000 BTC');
});

it('prefers the stored symbol when formatting crypto amounts', function (): void {
    $currency = new Currency([
        'code' => 'BTC',
        'name' => 'Bitcoin',
        'symbol' => '₿',
        'decimal_places' => 8,
        'type' => CurrencyType::Crypto,
    ]);

    expect($currency->formatAmount(100000000))->toBe('1.00000000 ₿');
});

it('uppercases stored currency codes', function (): void {
    $currency = new Currency(['code' => 'usd']);

    expect($currency->code)->toBe('USD');
});
