<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Localization;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Spatie\TranslationLoader\LanguageLine;
use Spatie\TranslationLoader\TranslationLoaderManager;

final class NamespacedTranslationLoaderManager extends TranslationLoaderManager
{
    /**
     * Allow configured translation loaders to override package translations.
     *
     * @param  string  $locale
     * @param  string  $group
     * @param  string|null  $namespace
     * @return array<mixed>
     */
    public function load($locale, $group, $namespace = null): array
    {
        $fileTranslations = parent::load($locale, $group, $namespace);

        if (! is_string($namespace) || $namespace === '' || $namespace === '*') {
            return $fileTranslations;
        }

        try {
            return array_replace_recursive(
                $fileTranslations,
                $this->getTranslationsForTranslationLoaders($locale, $group, $namespace),
            );
        } catch (QueryException $exception) {
            if ($this->translationTableIsMissing()) {
                return $fileTranslations;
            }

            throw $exception;
        }
    }

    private function translationTableIsMissing(): bool
    {
        $modelClass = config('translation-loader.model');

        if (! is_string($modelClass) || ! is_a($modelClass, LanguageLine::class, true)) {
            return false;
        }

        return ! Schema::hasTable((new $modelClass)->getTable());
    }
}
