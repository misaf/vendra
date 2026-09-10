<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Localization;

use Illuminate\Http\Request;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraLocalization\Contracts\LocaleResolver;

/**
 * Resolves the request locale from the current tenant's default active
 * language. Registered as the lowest-priority link in the localization chain,
 * so explicit request signals (user preference, query, route, header) win and
 * the tenant default acts as the baseline before `app.fallback_locale`.
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
