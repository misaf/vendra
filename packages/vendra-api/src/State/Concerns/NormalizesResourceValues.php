<?php

declare(strict_types=1);

namespace Misaf\VendraApi\State\Concerns;

trait NormalizesResourceValues
{
    /**
     * @param  array<array-key, mixed>  $translations
     * @return array<string, string>
     */
    protected function normalizeTranslations(array $translations): array
    {
        $normalizedTranslations = [];

        foreach ($translations as $locale => $translation) {
            if (is_string($locale) && is_string($translation)) {
                $normalizedTranslations[$locale] = $translation;
            }
        }

        return $normalizedTranslations;
    }

    /**
     * Normalize rich-text translations stored as Tiptap documents.
     *
     * @param  array<array-key, mixed>  $translations
     * @return array<string, array<array-key, mixed>|string>
     */
    protected function normalizeTranslationDocuments(array $translations): array
    {
        $normalizedTranslations = [];

        foreach ($translations as $locale => $translation) {
            if (! is_string($locale)) {
                continue;
            }

            if (is_string($translation) || is_array($translation)) {
                $normalizedTranslations[$locale] = $translation;
            }
        }

        return $normalizedTranslations;
    }
}
