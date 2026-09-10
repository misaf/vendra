<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency\Observers;

use Misaf\VendraCurrency\Models\Currency;

final class CurrencyObserver
{
    public function creating(Currency $currency): void
    {
        if (! $currency->active) {
            $currency->is_default = false;

            return;
        }

        if (! Currency::query()->active()->exists()) {
            $currency->is_default = true;
        }
    }

    public function saving(Currency $currency): void
    {
        if (! $currency->active) {
            $currency->is_default = false;

            return;
        }

        if ($currency->is_default) {
            Currency::query()
                ->where('is_default', true)
                ->whereKeyNot($currency->getKey())
                ->update(['is_default' => false]);

            return;
        }

        if ($currency->exists && $currency->getOriginal('is_default') === true) {
            $hasAnotherDefault = Currency::query()
                ->active()
                ->where('is_default', true)
                ->whereKeyNot($currency->getKey())
                ->exists();

            if (! $hasAnotherDefault) {
                $currency->is_default = true;
            }
        }
    }

    public function saved(Currency $currency): void
    {
        if ($currency->wasChanged(['active', 'is_default'])) {
            $this->ensureActiveDefault();
        }
    }

    public function deleted(Currency $currency): void
    {
        if (! $currency->is_default) {
            return;
        }

        $this->ensureActiveDefault();
    }

    private function ensureActiveDefault(): void
    {
        if (Currency::query()->active()->where('is_default', true)->exists()) {
            return;
        }

        Currency::query()
            ->active()
            ->ordered()
            ->first()
            ?->update(['is_default' => true]);
    }
}
