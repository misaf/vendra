<?php

declare(strict_types=1);

namespace App\Observers\Faq;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class FaqCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Faq\FaqCategory $faqCategory): void {}

    public function deleted(\App\Models\Faq\FaqCategory $faqCategory): void
    {
        $faqCategory->faqs()->delete();
    }

    public function forceDeleted(\App\Models\Faq\FaqCategory $faqCategory): void {}

    public function restored(\App\Models\Faq\FaqCategory $faqCategory): void {}

    public function updated(\App\Models\Faq\FaqCategory $faqCategory): void {}
}
