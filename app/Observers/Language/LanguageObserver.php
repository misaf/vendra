<?php

declare(strict_types=1);

namespace App\Observers\Language;

use App\Models\Language\Language;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LanguageObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Language "created" event.
     *
     * @param Language $language
     * @return void
     */
    public function created(Language $language): void {}

    /**
     * Handle the Language "deleted" event.
     *
     * @param Language $language
     * @return void
     */
    public function deleted(Language $language): void {}

    /**
     * Handle the Language "force deleted" event.
     *
     * @param Language $language
     * @return void
     */
    public function forceDeleted(Language $language): void {}

    /**
     * Handle the Language "restored" event.
     *
     * @param Language $language
     * @return void
     */
    public function restored(Language $language): void {}

    /**
     * Handle the Language "updated" event.
     *
     * @param Language $language
     * @return void
     */
    public function updated(Language $language): void {}
}
