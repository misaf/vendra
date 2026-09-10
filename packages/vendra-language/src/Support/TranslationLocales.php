<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Support;

use Misaf\VendraLanguage\Models\Language;

final class TranslationLocales
{
    /**
     * @return list<string>
     */
    public static function active(): array
    {
        $locales = Language::query()
            ->active()
            ->ordered()
            ->pluck('locale')
            ->filter(fn (mixed $locale): bool => is_string($locale))
            ->values()
            ->all();

        return $locales === []
            ? [config()->string('app.fallback_locale')]
            : array_values($locales);
    }

    /**
     * @param  array<string, string>  $translations
     * @return array<string, string>
     */
    public static function merge(array $translations = []): array
    {
        return array_replace(
            array_fill_keys(self::active(), ''),
            $translations,
        );
    }
}
