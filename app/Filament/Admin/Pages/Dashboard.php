<?php

declare(strict_types=1);

namespace App\Filament\Admin\Pages;

final class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?int $navigationSort = -2;

    protected static string $routePath = '/admin';

    public function getColumns(): int|array
    {
        return [
            'sm' => 1,
            'md' => 2,
            'lg' => 4,
        ];
    }

    public function getMaxContentWidth(): string
    {
        return 'screen-2xl';
    }
}
