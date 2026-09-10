<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

trait InteractsWithTranslatedGlobalSearch /** @phpstan-ignore trait.unused */
{
    /**
     * @return array<int, string>
     */
    abstract protected static function translatableGlobalSearchAttributes(): array;

    /**
     * @return array<int, string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        $locale = App::getLocale();

        return array_map(
            static fn (string $attribute): string => "{$attribute}->{$locale}",
            static::translatableGlobalSearchAttributes(),
        );
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        if (! method_exists($record, 'getTranslation')) {
            return '';
        }

        $title = $record->getTranslation('name', App::getLocale());

        return is_string($title) ? $title : '';
    }
}
