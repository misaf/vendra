<?php

declare(strict_types=1);

namespace App\Observers\Language;

use App\Models\Language\LanguageLine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LanguageLineObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the LanguageLine "created" event.
     *
     * @param LanguageLine $languageLine
     * @return void
     */
    public function created(LanguageLine $languageLine): void {}

    /**
     * Handle the LanguageLine "deleted" event.
     *
     * @param LanguageLine $languageLine
     * @return void
     */
    public function deleted(LanguageLine $languageLine): void {}

    /**
     * Handle the LanguageLine "force deleted" event.
     *
     * @param LanguageLine $languageLine
     * @return void
     */
    public function forceDeleted(LanguageLine $languageLine): void {}

    /**
     * Handle the LanguageLine "restored" event.
     *
     * @param LanguageLine $languageLine
     * @return void
     */
    public function restored(LanguageLine $languageLine): void {}

    /**
     * Handle the LanguageLine "updated" event.
     *
     * @param LanguageLine $languageLine
     * @return void
     */
    public function updated(LanguageLine $languageLine): void {}
}
