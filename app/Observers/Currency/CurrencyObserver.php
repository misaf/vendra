<?php

declare(strict_types=1);

namespace App\Observers\Currency;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class CurrencyObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Currency\Currency $currency): void {}

    public function deleted(\App\Models\Currency\Currency $currency): void {}

    public function forceDeleted(\App\Models\Currency\Currency $currency): void {}

    public function restored(\App\Models\Currency\Currency $currency): void {}

    public function updated(\App\Models\Currency\Currency $currency): void {}
}
