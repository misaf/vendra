<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Currency\Models\CurrencyCategory;

final class CurrencyCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the CurrencyCategory "created" event.
     *
     * @param CurrencyCategory $currencyCategory
     */
    public function created(CurrencyCategory $currencyCategory): void {}

    /**
     * Handle the CurrencyCategory "deleted" event.
     *
     * @param CurrencyCategory $currencyCategory
     */
    public function deleted(CurrencyCategory $currencyCategory): void
    {
        $currencyCategory->currencies()->delete();
    }

    /**
     * Handle the CurrencyCategory "force deleted" event.
     *
     * @param CurrencyCategory $currencyCategory
     */
    public function forceDeleted(CurrencyCategory $currencyCategory): void {}

    /**
     * Handle the CurrencyCategory "restored" event.
     *
     * @param CurrencyCategory $currencyCategory
     */
    public function restored(CurrencyCategory $currencyCategory): void {}

    /**
     * Handle the CurrencyCategory "updated" event.
     *
     * @param CurrencyCategory $currencyCategory
     */
    public function updated(CurrencyCategory $currencyCategory): void {}
}
