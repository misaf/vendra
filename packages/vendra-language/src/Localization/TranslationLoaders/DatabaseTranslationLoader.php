<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Localization\TranslationLoaders;

use Misaf\VendraLanguage\Contracts\NamespacedLanguageLine;
use Spatie\TranslationLoader\Exceptions\InvalidConfiguration;
use Spatie\TranslationLoader\LanguageLine;
use Spatie\TranslationLoader\TranslationLoaders\TranslationLoader;

final class DatabaseTranslationLoader implements TranslationLoader
{
    /**
     * The translator uses `*` for application groups, which language lines store as a null namespace.
     *
     * @return array<mixed>
     */
    public function loadTranslations(string $locale, string $group, ?string $namespace = null): array
    {
        if ($namespace === '' || $namespace === '*') {
            $namespace = null;
        }

        $modelClass = $this->configuredModelClass();

        if (is_a($modelClass, NamespacedLanguageLine::class, true)) {
            return $modelClass::getTranslationsForGroup($locale, $group, $namespace);
        }

        if ($namespace !== null) {
            return [];
        }

        return $modelClass::getTranslationsForGroup($locale, $group);
    }

    /**
     * @return class-string<LanguageLine>
     */
    private function configuredModelClass(): string
    {
        $modelClass = config('translation-loader.model');

        if (! is_string($modelClass) || ! is_a($modelClass, LanguageLine::class, true)) {
            throw InvalidConfiguration::invalidModel(is_string($modelClass) ? $modelClass : get_debug_type($modelClass));
        }

        return $modelClass;
    }
}
