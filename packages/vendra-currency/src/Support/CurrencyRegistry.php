<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Support;

use Illuminate\Support\Arr;
use Misaf\VendraCurrency\Enums\CurrencyType;
use Money\Currencies\CryptoCurrencies;
use Money\Currencies\ISOCurrencies;
use Money\Currency as MoneyCurrency;

/**
 * Currency reference data backed by `moneyphp/money` (ISO 4217 fiat
 * currencies) and `moneyphp/crypto-currencies` (crypto currencies).
 *
 * Fiat display names come from the bundled ISO resource; crypto currencies
 * carry no display name, so their code doubles as the name. When a code exists
 * in both catalogs the fiat entry wins.
 */
final class CurrencyRegistry
{
    /**
     * Every known currency as `code => entry`, fiat first, e.g.
     * `['USD' => ['code' => 'USD', 'name' => 'US Dollar', 'minor_unit' => 2, 'type' => CurrencyType::Fiat]]`.
     *
     * @return array<string, array{code: string, name: string, minor_unit: int, type: CurrencyType}>
     */
    public static function all(): array
    {
        return once(fn (): array => [...self::fiat(), ...self::crypto()]);
    }

    /**
     * Every selectable currency as `code => label`, e.g. `['USD' => 'US Dollar (USD)']`,
     * sorted by display name and optionally narrowed to a single currency type.
     *
     * @return array<string, string>
     */
    public static function options(?CurrencyType $type = null): array
    {
        return collect(self::all())
            ->when($type instanceof CurrencyType, fn ($entries) => $entries
                ->filter(fn (array $entry): bool => Arr::get($entry, 'type') === $type))
            ->map(function (array $entry): string {
                $name = (string) Arr::get($entry, 'name');
                $code = (string) Arr::get($entry, 'code');

                return $name === $code ? $code : "{$name} ({$code})";
            })
            ->sort()
            ->all();
    }

    /**
     * All known currency codes.
     *
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
        return Arr::get(self::get($code), 'name', mb_strtoupper($code));
    }

    public static function minorUnitFor(string $code): ?int
    {
        return Arr::get(self::get($code), 'minor_unit', null);
    }

    public static function typeFor(string $code): ?CurrencyType
    {
        return Arr::get(self::get($code), 'type', null);
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

        return collect($isoCurrencies)
            ->mapWithKeys(fn (MoneyCurrency $currency): array => [
                $currency->getCode() => [
                    'code' => $currency->getCode(),
                    'name' => $isoRecords[$currency->getCode()]['currency'] ?? $currency->getCode(),
                    'minor_unit' => $isoCurrencies->subunitFor($currency),
                    'type' => CurrencyType::Fiat,
                ],
            ])
            ->all();
    }

    /**
     * @return array<string, array{code: string, name: string, minor_unit: int, type: CurrencyType}>
     */
    private static function crypto(): array
    {
        $cryptoCurrencies = new CryptoCurrencies;

        return collect($cryptoCurrencies)
            ->mapWithKeys(fn (MoneyCurrency $currency): array => [
                $currency->getCode() => [
                    'code' => $currency->getCode(),
                    'name' => $currency->getCode(),
                    'minor_unit' => $cryptoCurrencies->subunitFor($currency),
                    'type' => CurrencyType::Crypto,
                ],
            ])
            ->all();
    }
}
