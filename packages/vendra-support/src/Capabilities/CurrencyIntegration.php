<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Misaf\VendraSupport\Contracts\CurrencyResolver;
use Throwable;

final class CurrencyIntegration
{
    public static function defaultCode(): string
    {
        try {
            return self::resolver()->defaultCode();
        } catch (Throwable) {
            return self::fallbackResolver()->defaultCode();
        }
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        try {
            return self::resolver()->options();
        } catch (Throwable) {
            return self::fallbackResolver()->options();
        }
    }

    /**
     * @return list<string>
     */
    public static function activeCurrencyCodes(): array
    {
        try {
            return self::resolver()->activeCodes();
        } catch (Throwable) {
            return self::fallbackResolver()->activeCodes();
        }
    }

    public static function isAvailable(): bool
    {
        try {
            return self::resolver()->available();
        } catch (Throwable) {
            return false;
        }
    }

    private static function resolver(): CurrencyResolver
    {
        if (app()->bound(CurrencyResolver::class)) {
            return resolve(CurrencyResolver::class);
        }

        return self::fallbackResolver();
    }

    private static function fallbackResolver(): CurrencyResolver
    {
        return new NullCurrencyResolver;
    }
}
