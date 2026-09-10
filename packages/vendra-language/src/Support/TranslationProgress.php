<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Misaf\VendraLanguage\Models\LanguageLine;

final class TranslationProgress
{
    /** @var list<string>|null */
    private ?array $activeLocales = null;

    /** @var array<string, array{translated: int, total: int, remaining: int, percentage: int, missing_locales: list<string>}> */
    private array $languageLineProgress = [];

    /** @var Collection<int, LanguageLine>|null */
    private ?Collection $languageLines = null;

    /** @var array<string, array{translated: int, total: int, remaining: int, percentage: int, missing_locales: list<string>}> */
    private array $localeProgress = [];

    /**
     * @return array{translated: int, total: int, remaining: int, percentage: int, missing_locales: list<string>}
     */
    public function forLocale(string $locale): array
    {
        if (array_key_exists($locale, $this->localeProgress)) {
            return $this->localeProgress[$locale];
        }

        $languageLines = $this->languageLines();
        $translated = $languageLines
            ->filter(fn (LanguageLine $languageLine): bool => $this->hasTranslation($languageLine, $locale))
            ->count();

        return $this->localeProgress[$locale] = $this->summarize(
            translated: $translated,
            total: $languageLines->count(),
        );
    }

    /**
     * @return array{translated: int, total: int, remaining: int, percentage: int, missing_locales: list<string>}
     */
    public function forLanguageLine(LanguageLine $languageLine): array
    {
        $modelKey = $languageLine->getKey();
        $cacheKey = is_int($modelKey) || is_string($modelKey)
            ? (string) $modelKey
            : 'object:'.spl_object_id($languageLine);

        if (array_key_exists($cacheKey, $this->languageLineProgress)) {
            return $this->languageLineProgress[$cacheKey];
        }

        $activeLocales = $this->activeLocales();
        $missingLocales = array_values(array_filter(
            $activeLocales,
            fn (string $locale): bool => ! $this->hasTranslation($languageLine, $locale),
        ));

        return $this->languageLineProgress[$cacheKey] = $this->summarize(
            translated: count($activeLocales) - count($missingLocales),
            total: count($activeLocales),
            missingLocales: $missingLocales,
        );
    }

    private function hasTranslation(LanguageLine $languageLine, string $locale): bool
    {
        $translation = $languageLine->text[$locale] ?? null;

        return is_string($translation) && Str::of($translation)->trim()->isNotEmpty();
    }

    /**
     * @return list<string>
     */
    private function activeLocales(): array
    {
        return $this->activeLocales ??= TranslationLocales::active();
    }

    /**
     * @return Collection<int, LanguageLine>
     */
    private function languageLines(): Collection
    {
        return $this->languageLines ??= LanguageLine::query()->get(['id', 'text']);
    }

    /**
     * @param  list<string>  $missingLocales
     * @return array{translated: int, total: int, remaining: int, percentage: int, missing_locales: list<string>}
     */
    private function summarize(int $translated, int $total, array $missingLocales = []): array
    {
        return [
            'translated' => $translated,
            'total' => $total,
            'remaining' => $total - $translated,
            'percentage' => $total === 0 ? 0 : (int) round(($translated / $total) * 100),
            'missing_locales' => $missingLocales,
        ];
    }
}
