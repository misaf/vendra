<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Capabilities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Contracts\CurrencyResolver;
use Throwable;

final class EloquentCurrencyResolver implements CurrencyResolver
{
    /**
     * @param  class-string<Model>  $currencyModel
     */
    public function __construct(
        private readonly string $currencyModel,
        private readonly CurrencyResolver $fallback = new NullCurrencyResolver,
        private readonly string $codeColumn = 'code',
        private readonly string $nameColumn = 'name',
        private readonly string $activeColumn = 'active',
        private readonly string $defaultColumn = 'is_default',
        private readonly string $positionColumn = 'position',
    ) {}

    public function available(): bool
    {
        return true;
    }

    public function defaultCode(): string
    {
        try {
            $defaultCode = $this->query()
                ->where($this->activeColumn, true)
                ->where($this->defaultColumn, true)
                ->value($this->codeColumn);

            if (is_string($defaultCode) && $defaultCode !== '') {
                return $defaultCode;
            }
        } catch (Throwable) {
            return $this->fallback->defaultCode();
        }

        return $this->fallback->defaultCode();
    }

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        try {
            $options = $this->query()
                ->where($this->activeColumn, true)
                ->orderBy($this->positionColumn, 'desc')
                ->pluck($this->nameColumn, $this->codeColumn)
                ->mapWithKeys(fn (mixed $name, mixed $code): array => is_string($code) && $code !== ''
                    ? [$code => is_string($name) ? $name : $code]
                    : [])
                ->all();

            if ($options !== []) {
                return $options;
            }
        } catch (Throwable) {
            return $this->fallback->options();
        }

        return $this->fallback->options();
    }

    /**
     * @return list<string>
     */
    public function activeCodes(): array
    {
        try {
            $currencyCodes = $this->query()
                ->where($this->activeColumn, true)
                ->pluck($this->codeColumn)
                ->filter(fn (mixed $code): bool => is_string($code) && $code !== '')
                ->values()
                ->all();

            return $currencyCodes !== [] ? array_values($currencyCodes) : $this->fallback->activeCodes();
        } catch (Throwable) {
            return $this->fallback->activeCodes();
        }
    }

    /**
     * @return Builder<Model>
     */
    private function query(): Builder
    {
        return (new $this->currencyModel)->newQuery();
    }
}
