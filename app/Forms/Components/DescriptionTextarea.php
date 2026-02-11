<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Filament\Forms\Components\Textarea;

final class DescriptionTextarea
{
    public static function make(string $name): Textarea
    {
        return Textarea::make($name)
            ->columnSpanFull()
            ->label(__('form.description'))
            ->rows(5);
    }
}
