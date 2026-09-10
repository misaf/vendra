<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Support;

use Illuminate\Support\Str;
use Symfony\Component\Intl\Locales as IntlLocales;

/**
 * Locale reference data backed by `symfony/intl` (bundled ICU data).
 *
 * Values are exposed in web/BCP-47 form (hyphen separated, e.g. `pt-BR`) to
 * match Laravel locale strings and the `LanguageLine` translation keys, while
 * ICU works internally with the underscore form (`pt_BR`).
 */
final class Locales
{
    /**
     * Upper bound on a stored locale tag, aligned with the `locale` column
     * and the form input. Longer ICU locales (e.g. `zh-Hant-TW`) are excluded.
     */
    private const int MAX_LENGTH = 8;

    /**
     * Every selectable locale as `value => label`, e.g. `['pt-BR' => 'Portuguese (pt-BR)']`,
     * rendered in the given display locale and sorted alphabetically.
     *
     * @return array<string, string>
     */
    public static function options(?string $displayLocale = null): array
    {
        return collect(self::names(self::all(), $displayLocale))
            ->sort()
            ->all();
    }

    /**
     * The given locale tags as `value => label`, e.g. `['en' => 'English (en)']`.
     *
     * @param  array<int, string>  $locales
     * @return array<string, string>
     */
    public static function names(array $locales, ?string $displayLocale = null): array
    {
        $display = $displayLocale ?? app()->getLocale();

        return collect($locales)
            ->mapWithKeys(fn (string $locale): array => [
                $locale => self::name($locale, $display)." ({$locale})",
            ])
            ->all();
    }

    /**
     * The display name for a single locale, falling back to the tag itself.
     */
    public static function name(string $locale, ?string $displayLocale = null): string
    {
        $display = $displayLocale ?? app()->getLocale();

        return rescue(
            fn (): string => IntlLocales::getName(self::toIcu($locale), $display),
            $locale,
            report: false,
        );
    }

    /**
     * All supported locale tags in web form, e.g. `en`, `de`, `pt-BR`.
     *
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_values(self::catalog());
    }

    /**
     * The canonical, ICU-supported platform locale catalog.
     *
     * @return list<string>
     */
    public static function configured(): array
    {
        $locales = collect(config()->array('vendra-language.locales', []))
            ->map(fn (mixed $locale): ?string => is_string($locale) ? self::normalize($locale) : null)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($locales !== []) {
            return array_values($locales);
        }

        $fallback = self::normalize(config()->string('app.fallback_locale'));

        return $fallback === null ? [] : [$fallback];
    }

    /**
     * Empty translation values keyed by the configured locale catalog.
     *
     * @return array<string, string>
     */
    public static function translationDefaults(): array
    {
        return array_fill_keys(self::configured(), '');
    }

    /**
     * Whether the given locale tag maps to a known ICU locale.
     */
    public static function isSupported(string $locale): bool
    {
        return filled(self::normalize($locale));
    }

    /**
     * Normalize arbitrary input to a supported web-form locale tag, or `null`
     * when it does not resolve to a known locale. Matching is case-insensitive
     * and separator-agnostic, e.g. `pt_br` resolves to `pt-BR`.
     */
    public static function normalize(string $locale): ?string
    {
        $needle = Str::of($locale)->replace('_', '-')->lower()->value();

        return self::catalog()[$needle] ?? null;
    }

    /**
     * The supported locales as `lowercase web tag => canonical web tag`,
     * memoized for the process lifetime (the underlying ICU data is immutable).
     *
     * @return array<string, string>
     */
    private static function catalog(): array
    {
        return once(fn (): array => collect(IntlLocales::getLocales())
            ->map(fn (string $locale): string => self::toWeb($locale))
            ->reject(fn (string $locale): bool => Str::length($locale) > self::MAX_LENGTH)
            ->mapWithKeys(fn (string $locale): array => [Str::lower($locale) => $locale])
            ->all());
    }

    private static function toWeb(string $icuLocale): string
    {
        return Str::replace('_', '-', $icuLocale);
    }

    private static function toIcu(string $webLocale): string
    {
        return Str::replace('-', '_', $webLocale);
    }
}
