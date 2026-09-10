<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Localization;

use Illuminate\Http\Request;
use Misaf\VendraLanguage\Support\Locales;
use Misaf\VendraLanguage\Support\TranslationLocales;
use Misaf\VendraLocalization\Contracts\LocaleResolver;

final class LanguageSwitchLocaleResolver implements LocaleResolver
{
    public function resolve(Request $request): ?string
    {
        $locale = $request->hasSession()
            ? $request->session()->get('locale')
            : null;

        $locale ??= $request->cookie('filament_language_switch_locale');

        if (! is_string($locale) || null === ($locale = Locales::normalize($locale))) {
            return null;
        }

        return in_array($locale, TranslationLocales::active(), true) ? $locale : null;
    }
}
