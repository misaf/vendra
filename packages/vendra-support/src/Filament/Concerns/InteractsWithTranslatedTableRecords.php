<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Livewire\Component as Livewire;

trait InteractsWithTranslatedTableRecords /** @phpstan-ignore trait.unused */
{
    /**
     * Resolve a translated attribute for the table's active locale.
     *
     * The record must use Spatie\Translatable\HasTranslations.
     */
    protected static function translatedAttribute(Model $record, string $attribute, Livewire $livewire): string
    {
        $translation = $record->getTranslation($attribute, static::activeLocale($livewire));

        return is_string($translation) ? $translation : '';
    }

    protected static function activeLocale(Livewire $livewire): string
    {
        $locale = data_get($livewire, 'activeLocale');

        return is_string($locale) && $locale !== '' ? $locale : App::getLocale();
    }

    protected static function integerAttribute(Model $record, string $attribute): int
    {
        $value = $record->getAttribute($attribute);

        if (is_int($value)) {
            return $value;
        }

        return is_numeric($value) ? (int) $value : 0;
    }
}
