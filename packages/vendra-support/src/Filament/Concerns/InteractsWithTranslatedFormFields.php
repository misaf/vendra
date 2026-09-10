<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Illuminate\Support\Facades\App;
use Livewire\Component as Livewire;

trait InteractsWithTranslatedFormFields /** @phpstan-ignore trait.unused */
{
    /**
     * Resolve the locale whose translation the form's unique rules validate against.
     */
    protected static function activeFormLocale(Livewire $livewire): string
    {
        $locale = data_get($livewire, 'activeLocale');

        return is_string($locale) && $locale !== '' ? $locale : App::getLocale();
    }
}
