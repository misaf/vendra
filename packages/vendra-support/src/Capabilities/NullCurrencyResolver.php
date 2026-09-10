<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Contracts\CurrencyResolver;

final class NullCurrencyResolver implements CurrencyResolver
{
    public function available(): bool
    {
        return false;
    }

    public function defaultCode(): string
    {
        $configuredCurrency = Config::get('money.defaultCurrency', Config::get('app.currency', 'USD'));

        if (is_string($configuredCurrency) && $configuredCurrency !== '') {
            return $configuredCurrency;
        }

        return 'USD';
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        $defaultCode = $this->defaultCode();

        return [$defaultCode => $defaultCode];
    }

    /**
     * @return list<string>
     */
    public function activeCodes(): array
    {
        return [$this->defaultCode()];
    }
}
