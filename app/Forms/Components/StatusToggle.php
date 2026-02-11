<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Filament\Forms\Components\Toggle;

final class StatusToggle
{
    public static function make(string $name): Toggle
    {
        return Toggle::make($name)
            ->columnSpanFull()
            ->default(true)
            ->label(__('form.status'))
            ->onIcon('heroicon-m-bolt')
            ->rules('required');
    }
}
