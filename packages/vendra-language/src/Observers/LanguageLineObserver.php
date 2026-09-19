<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Observers;

use Misaf\VendraLanguage\Models\LanguageLine;

/**
 * Flush the translation cache using the line's original attributes, before they change.
 */
final class LanguageLineObserver
{
    public function updating(LanguageLine $languageLine): void
    {
        $languageLine->flushOriginalTranslationCache();
    }
}
