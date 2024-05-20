<?php

declare(strict_types=1);

namespace App\Observers\Faq;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

final class FaqObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Faq\Faq $faq): void {}

    public function deleted(\App\Models\Faq\Faq $faq): void
    {
        Cache::forget('faq_row_count');
    }

    public function forceDeleted(\App\Models\Faq\Faq $faq): void {}

    public function restored(\App\Models\Faq\Faq $faq): void {}

    public function saved(\App\Models\Faq\Faq $product): void
    {
        Cache::forget('faq_row_count');
    }

    public function updated(\App\Models\Faq\Faq $faq): void {}
}
