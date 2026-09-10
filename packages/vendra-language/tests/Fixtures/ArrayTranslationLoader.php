<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Tests\Fixtures;

use Spatie\TranslationLoader\TranslationLoaders\TranslationLoader;

final class ArrayTranslationLoader implements TranslationLoader
{
    /** @var array<string, array<string, string>> */
    public static array $translations = [];

    /**
     * @return array<mixed>
     */
    public function loadTranslations(string $locale, string $group, ?string $namespace = null): array
    {
        $key = $namespace === null ? $group : "{$namespace}::{$group}";

        return self::$translations[$key] ?? [];
    }
}
