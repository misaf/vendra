<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Filament\Forms\Components\TextInput;

final class NameTextInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->columnSpan([
                'lg' => 1,
            ])
            ->label(__('form.name'));
    }
}
