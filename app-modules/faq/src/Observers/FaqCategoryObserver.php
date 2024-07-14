<?php

declare(strict_types=1);

namespace Termehsoft\Faq\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\Faq\Models\FaqCategory;

final class FaqCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the FaqCategory "created" event.
     *
     * @param FaqCategory $faqCategory
     */
    public function created(FaqCategory $faqCategory): void {}

    /**
     * Handle the FaqCategory "deleted" event.
     *
     * @param FaqCategory $faqCategory
     */
    public function deleted(FaqCategory $faqCategory): void
    {
        $faqCategory->faqs()->delete();
    }

    /**
     * Handle the FaqCategory "force deleted" event.
     *
     * @param FaqCategory $faqCategory
     */
    public function forceDeleted(FaqCategory $faqCategory): void {}

    /**
     * Handle the FaqCategory "restored" event.
     *
     * @param FaqCategory $faqCategory
     */
    public function restored(FaqCategory $faqCategory): void {}

    /**
     * Handle the FaqCategory "updated" event.
     *
     * @param FaqCategory $faqCategory
     */
    public function updated(FaqCategory $faqCategory): void {}
}
