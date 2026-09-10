<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Misaf\VendraSupport\Capabilities\CurrencyIntegration;
use Misaf\VendraSupport\Capabilities\NullCurrencyResolver;
use Misaf\VendraSupport\Contracts\CurrencyResolver;

it('falls back to configured currency through the null resolver', function (): void {
    config(['app.currency' => 'GBP']);
    config(['money' => Arr::except(config('money', []), ['defaultCurrency'])]);

    app()->instance(CurrencyResolver::class, new class implements CurrencyResolver
    {
        public function available(): bool
        {
            throw new RuntimeException('Resolver unavailable.');
        }

        public function defaultCode(): string
        {
            throw new RuntimeException('Resolver unavailable.');
        }

        public function options(): array
        {
            throw new RuntimeException('Resolver unavailable.');
        }

        public function activeCodes(): array
        {
            throw new RuntimeException('Resolver unavailable.');
        }
    });

    expect(CurrencyIntegration::isAvailable())->toBeFalse()
        ->and(CurrencyIntegration::defaultCode())->toBe('GBP')
        ->and(CurrencyIntegration::options())->toBe(['GBP' => 'GBP'])
        ->and(CurrencyIntegration::activeCurrencyCodes())->toBe(['GBP']);
});

it('uses the bound currency resolver when available', function (): void {
    app()->instance(CurrencyResolver::class, new class implements CurrencyResolver
    {
        public function available(): bool
        {
            return true;
        }

        public function defaultCode(): string
        {
            return 'EUR';
        }

        public function options(): array
        {
            return [
                'EUR' => 'Euro',
                'USD' => 'US Dollar',
            ];
        }

        public function activeCodes(): array
        {
            return ['EUR', 'USD'];
        }
    });

    expect(CurrencyIntegration::isAvailable())->toBeTrue()
        ->and(CurrencyIntegration::defaultCode())->toBe('EUR')
        ->and(CurrencyIntegration::options())->toBe([
            'EUR' => 'Euro',
            'USD' => 'US Dollar',
        ])
        ->and(CurrencyIntegration::activeCurrencyCodes())->toBe(['EUR', 'USD']);
});

it('falls back when the bound resolver throws', function (): void {
    config(['app.currency' => 'CAD']);
    config(['money' => Arr::except(config('money', []), ['defaultCurrency'])]);

    app()->instance(CurrencyResolver::class, new class implements CurrencyResolver
    {
        public function available(): bool
        {
            throw new RuntimeException('Resolver failed.');
        }

        public function defaultCode(): string
        {
            throw new RuntimeException('Resolver failed.');
        }

        public function options(): array
        {
            throw new RuntimeException('Resolver failed.');
        }

        public function activeCodes(): array
        {
            throw new RuntimeException('Resolver failed.');
        }
    });

    expect(CurrencyIntegration::isAvailable())->toBeFalse()
        ->and(CurrencyIntegration::defaultCode())->toBe('CAD')
        ->and(CurrencyIntegration::options())->toBe(['CAD' => 'CAD'])
        ->and(CurrencyIntegration::activeCurrencyCodes())->toBe(['CAD']);
});

it('uses the support null resolver fallback shape', function (): void {
    config(['app.currency' => 'USD']);

    $resolver = new NullCurrencyResolver;

    expect($resolver->available())->toBeFalse()
        ->and($resolver->options())->toBe([$resolver->defaultCode() => $resolver->defaultCode()])
        ->and($resolver->activeCodes())->toBe([$resolver->defaultCode()]);
});
