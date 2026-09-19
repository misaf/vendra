<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Symfony\Component\Intl\Countries as IntlCountries;

final class Countries
{
    /**
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_values(IntlCountries::getCountryCodes());
    }

    public static function name(string $countryCode, ?string $displayLocale = null): string
    {
        return IntlCountries::getName($countryCode, $displayLocale ?? app()->getLocale());
    }

    /**
     * @return array<string, string>
     */
    public static function options(?string $displayLocale = null): array
    {
        return IntlCountries::getNames($displayLocale ?? app()->getLocale());
    }
}
