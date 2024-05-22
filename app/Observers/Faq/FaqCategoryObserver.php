<?php

declare(strict_types=1);

namespace App\Observers\Faq;

use App\Models\Faq\FaqCategory;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class FaqCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the FaqCategory "created" event.
     *
     * @param FaqCategory $faqCategory
     * @return void
     */
    public function created(FaqCategory $faqCategory): void {}

    /**
     * Handle the FaqCategory "deleted" event.
     *
     * @param FaqCategory $faqCategory
     * @return void
     */
    public function deleted(FaqCategory $faqCategory): void
    {
        $faqCategory->faqs()->delete();
    }

    /**
     * Handle the FaqCategory "force deleted" event.
     *
     * @param FaqCategory $faqCategory
     * @return void
     */
    public function forceDeleted(FaqCategory $faqCategory): void {}

    /**
     * Handle the FaqCategory "restored" event.
     *
     * @param FaqCategory $faqCategory
     * @return void
     */
    public function restored(FaqCategory $faqCategory): void {}

    /**
     * Handle the FaqCategory "updated" event.
     *
     * @param FaqCategory $faqCategory
     * @return void
     */
    public function updated(FaqCategory $faqCategory): void {}
}
