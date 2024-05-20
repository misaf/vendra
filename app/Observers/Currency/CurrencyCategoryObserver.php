<?php

declare(strict_types=1);

namespace App\Observers\Currency;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CurrencyCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Currency\CurrencyCategory $currencyCategory): void {}

    public function deleted(\App\Models\Currency\CurrencyCategory $currencyCategory): void
    {
        $currencyCategory->currencies()->delete();
    }

    public function forceDeleted(\App\Models\Currency\CurrencyCategory $currencyCategory): void {}

    public function restored(\App\Models\Currency\CurrencyCategory $currencyCategory): void {}

    public function updated(\App\Models\Currency\CurrencyCategory $currencyCategory): void {}
}
