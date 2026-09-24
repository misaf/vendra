<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Support;

use Misaf\VendraCurrency\Enums\CurrencyType;
use Money\Currencies\CryptoCurrencies;
use Money\Currencies\ISOCurrencies;

/**
 * Crypto currencies use their code as the name; fiat wins when a code is in both.
 */
final class CurrencyRegistry
{
    /**
     * Get every known currency keyed by code, fiat first.
     *
     * @return array<string, array{code: string, name: string, minor_unit: int, type: CurrencyType}>
     */
    public static function all(): array
    {
        return once(fn (): array => [...self::fiat(), ...self::crypto()]);
    }

    /**
     * Get the currency options, such as `['USD' => 'US Dollar (USD)']`, sorted by name.
     *
     * @return array<string, string>
     */
    public static function options(?CurrencyType $type = null): array
    {
        $options = [];

        foreach (self::all() as $code => ['name' => $name, 'type' => $entryType]) {
            if ($type instanceof CurrencyType && $entryType !== $type) {
                continue;
            }

            $options[$code] = $name === $code ? $code : "{$name} ({$code})";
        }

        asort($options);

        return $options;
    }

    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::all());
    }

    public static function isSupported(string $code): bool
    {
        return isset(self::all()[mb_strtoupper($code)]);
    }

    /**
     * @return array{code: string, name: string, minor_unit: int, type: CurrencyType}|null
     */
    public static function get(string $code): ?array
    {
        return self::all()[mb_strtoupper($code)] ?? null;
    }

    public static function nameFor(string $code): string
    {
        $currency = self::get($code);

        if ($currency === null) {
            return mb_strtoupper($code);
        }

        ['name' => $name] = $currency;

        return $name;
    }

    public static function minorUnitFor(string $code): ?int
    {
        $currency = self::get($code);

        if ($currency === null) {
            return null;
        }

        ['minor_unit' => $minorUnit] = $currency;

        return $minorUnit;
    }

    public static function typeFor(string $code): ?CurrencyType
    {
        $currency = self::get($code);

        if ($currency === null) {
            return null;
        }

        ['type' => $type] = $currency;

        return $type;
    }

    /**
     * @return array<string, array{code: string, name: string, minor_unit: int, type: CurrencyType}>
     */
    private static function fiat(): array
    {
        $isoCurrencies = new ISOCurrencies;

        /** @var array<string, array{alphabeticCode: string, currency: string, minorUnit: int, numericCode: int}> $isoRecords */
        $isoRecords = require config()->string(
            'money.isoCurrenciesPath',
            dirname(__DIR__, 3).'/moneyphp/money/resources/currency.php',
        );

        $currencies = [];

        foreach ($isoCurrencies as $currency) {
            $currencies[$currency->getCode()] = [
                'code' => $currency->getCode(),
                'name' => $isoRecords[$currency->getCode()]['currency'] ?? $currency->getCode(),
                'minor_unit' => $isoCurrencies->subunitFor($currency),
                'type' => CurrencyType::Fiat,
            ];
        }

        return $currencies;
    }

    /**
     * @return array<string, array{code: string, name: string, minor_unit: int, type: CurrencyType}>
     */
    private static function crypto(): array
    {
        $cryptoCurrencies = new CryptoCurrencies;

        $currencies = [];

        foreach ($cryptoCurrencies as $currency) {
            $currencies[$currency->getCode()] = [
                'code' => $currency->getCode(),
                'name' => $currency->getCode(),
                'minor_unit' => $cryptoCurrencies->subunitFor($currency),
                'type' => CurrencyType::Crypto,
            ];
        }

        return $currencies;
    }
}
