<?php

declare(strict_types=1);

namespace App\Filament\User\Pages;

final class Dashboard extends \Filament\Pages\Dashboard
{
    protected static ?int $navigationSort = -2;

    protected static string $routePath = '/';

    public function getColumns(): int|array
    {
        return 4;
    }
}
