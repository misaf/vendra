<?php

declare(strict_types=1);

namespace Termehsoft\Faq\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Termehsoft\Faq\Models\FaqCategory;

#[ObservedBy([FaqCategoryObserver::class])]
final class FaqCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the FaqCategory "deleted" event.
     *
     * @param FaqCategory $faqCategory
     * @return void
     */
    public function deleted(FaqCategory $faqCategory): void
    {
        $faqCategory->faqs()->delete();

        $this->clearCaches($faqCategory);
    }

    /**
     * Handle the FaqCategory "saved" event.
     *
     * @param FaqCategory $faqCategory
     * @return void
     */
    public function saved(FaqCategory $faqCategory): void
    {
        $this->clearCaches($faqCategory);
    }

    /**
     * Clear relevant caches.
     *
     * @param FaqCategory $faqCategory
     * @return void
     */
    private function clearCaches(FaqCategory $faqCategory): void
    {
        $this->forgetRowCountCache();
    }

    /**
     * Forget the currenncy category row count cache.
     *
     * @return void
     */
    private function forgetRowCountCache(): void
    {
        Cache::forget('faq-category-row-count');
    }
}
