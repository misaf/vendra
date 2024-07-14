<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Currency\Models\Currency;

final class CurrencyObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Currency "created" event.
     *
     * @param Currency $currency
     */
    public function created(Currency $currency): void {}

    /**
     * Handle the Currency "deleted" event.
     *
     * @param Currency $currency
     */
    public function deleted(Currency $currency): void {}

    /**
     * Handle the Currency "force deleted" event.
     *
     * @param Currency $currency
     */
    public function forceDeleted(Currency $currency): void {}

    /**
     * Handle the Currency "restored" event.
     *
     * @param Currency $currency
     */
    public function restored(Currency $currency): void {}

    /**
     * Handle the Currency "updated" event.
     *
     * @param Currency $currency
     */
    public function updated(Currency $currency): void {}
}
