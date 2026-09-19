<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Localization;

use Illuminate\Http\Request;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraLocalization\Contracts\LocaleResolver;

/**
 * Registered last in the chain, so explicit request signals win.
 */
final class TenantLocaleResolver implements LocaleResolver
{
    public function resolve(Request $request): ?string
    {
        $locale = Language::query()
            ->active()
            ->where('is_default', true)
            ->ordered()
            ->value('locale');

        return is_string($locale) && $locale !== '' ? $locale : null;
    }
}
