<?php

declare(strict_types=1);

namespace Termehsoft\Language\Observers;

use App\Contract\Language;
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
     */
    public function created(Language $language): void {}

    /**
     * Handle the Language "deleted" event.
     *
     * @param Language $language
     */
    public function deleted(Language $language): void {}

    /**
     * Handle the Language "force deleted" event.
     *
     * @param Language $language
     */
    public function forceDeleted(Language $language): void {}

    /**
     * Handle the Language "restored" event.
     *
     * @param Language $language
     */
    public function restored(Language $language): void {}

    /**
     * Handle the Language "updated" event.
     *
     * @param Language $language
     */
    public function updated(Language $language): void {}
}
