<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Misaf\VendraLanguage\Contracts\NamespacedLanguageLine;
use Spatie\TranslationLoader\LanguageLine;

final class CustomNamespacedLanguageLine extends LanguageLine implements NamespacedLanguageLine
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public static function getTranslationsForGroup(
        string $locale,
        string $group,
        ?string $namespace = null,
    ): array {
        return ['custom' => ($namespace ?? 'application').":{$locale}:{$group}"];
    }
}
