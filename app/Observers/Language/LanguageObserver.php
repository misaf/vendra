<?php

declare(strict_types=1);

namespace App\Observers\Language;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LanguageObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Language\Language $language): void {}

    public function deleted(\App\Models\Language\Language $language): void {}

    public function forceDeleted(\App\Models\Language\Language $language): void {}

    public function restored(\App\Models\Language\Language $language): void {}

    public function updated(\App\Models\Language\Language $language): void {}
}
