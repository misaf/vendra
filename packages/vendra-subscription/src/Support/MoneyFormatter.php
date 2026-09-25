<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Support;

use Cknow\Money\Money;
use Illuminate\Support\Number;
use Throwable;

final class MoneyFormatter
{
    /**
     * Format minor units for display, such as `$29.00`.
     *
     * Unknown currencies fall back to a plain number and code.
     */
    public static function format(int $amount, ?string $currencyCode): string
    {
        if ($currencyCode === null) {
            return self::plain($amount);
        }

        try {
            return new Money($amount, $currencyCode)->format();
        } catch (Throwable) {
            return self::plain($amount).' '.$currencyCode;
        }
    }

    /**
     * Format the amount as a plain localized number, or the raw value on failure.
     */
    private static function plain(int $amount): string
    {
        return Number::format($amount, locale: 'en') ?: (string) $amount;
    }
}
