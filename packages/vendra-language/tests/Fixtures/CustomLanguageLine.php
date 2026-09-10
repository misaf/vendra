<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Tests\Fixtures;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\TranslationLoader\LanguageLine;

final class CustomLanguageLine extends LanguageLine
{
    use HasFactory;

    /**
     * @return array<string, string>
     */
    public static function getTranslationsForGroup(string $locale, string $group): array
    {
        return ['custom' => "{$locale}:{$group}"];
    }
}
