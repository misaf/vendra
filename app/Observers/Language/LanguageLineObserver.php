<?php

declare(strict_types=1);

namespace App\Observers\Language;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class LanguageLineObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Language\LanguageLine $languageLine): void {}

    public function deleted(\App\Models\Language\LanguageLine $languageLine): void {}

    public function forceDeleted(\App\Models\Language\LanguageLine $languageLine): void {}

    public function restored(\App\Models\Language\LanguageLine $languageLine): void {}

    public function updated(\App\Models\Language\LanguageLine $languageLine): void {}
}
