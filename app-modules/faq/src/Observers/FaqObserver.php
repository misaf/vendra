<?php

declare(strict_types=1);

namespace Termehsoft\Faq\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Termehsoft\Faq\Models\Faq;

#[ObservedBy([FaqObserver::class])]
final class FaqObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the Faq "deleted" event.
     *
     * @param Faq $faq
     * @return void
     */
    public function deleted(Faq $faq): void
    {
        $this->clearCaches($faq);
    }

    /**
     * Handle the Faq "saved" event.
     *
     * @param Faq $faq
     * @return void
     */
    public function saved(Faq $faq): void
    {
        $this->clearCaches($faq);
    }

    /**
     * Clear relevant caches.
     *
     * @param Faq $faq
     * @return void
     */
    private function clearCaches(Faq $faq): void
    {
        $this->forgetRowCountCache();
    }

    /**
     * Forget the faq row count cache.
     *
     * @return void
     */
    private function forgetRowCountCache(): void
    {
        Cache::forget('faq-row-count');
    }
}
