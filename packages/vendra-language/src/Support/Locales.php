<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Support;

use Illuminate\Support\Str;
use Symfony\Component\Intl\Locales as IntlLocales;

/**
 * Locale data from `symfony/intl`, in hyphenated web form such as `pt-BR`.
 */
final class Locales
{
    /**
     * The maximum locale tag length, matching the `locale` column.
     */
    private const int MAX_LENGTH = 8;

    /**
     * Get the locale options, such as `['pt-BR' => 'Portuguese (pt-BR)']`, sorted by name.
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
     * Get the labels for the given locales, such as `['en' => 'English (en)']`.
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
     * Get a locale's display name, falling back to the tag.
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
     * @return array<int, string>
     */
    public static function all(): array
    {
        return array_values(self::catalog());
    }

    /**
     * Get the platform's configured locales that ICU supports.
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
     * @return array<string, string>
     */
    public static function translationDefaults(): array
    {
        return array_fill_keys(self::configured(), '');
    }

    public static function isSupported(string $locale): bool
    {
        return filled(self::normalize($locale));
    }

    /**
     * Normalize input to a supported locale tag, so `pt_br` becomes `pt-BR`.
     */
    public static function normalize(string $locale): ?string
    {
        $needle = Str::of($locale)->replace('_', '-')->lower()->value();

        return self::catalog()[$needle] ?? null;
    }

    /**
     * Get the supported tags keyed by their lowercase form, memoized.
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
