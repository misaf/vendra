<?php

declare(strict_types=1);

namespace App\Observers\Currency;

use App\Models\Currency\Currency;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CurrencyObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Currency "created" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function created(Currency $currency): void {}

    /**
     * Handle the Currency "deleted" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function deleted(Currency $currency): void {}

    /**
     * Handle the Currency "force deleted" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function forceDeleted(Currency $currency): void {}

    /**
     * Handle the Currency "restored" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function restored(Currency $currency): void {}

    /**
     * Handle the Currency "updated" event.
     *
     * @param Currency $currency
     * @return void
     */
    public function updated(Currency $currency): void {}
}
