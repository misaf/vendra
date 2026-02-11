<?php

declare(strict_types=1);

namespace App\Filament\Admin\Clusters\Settings;

use Filament\Clusters\Cluster;

final class SettingsCluster extends Cluster
{
    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'settings';

    public static function getNavigationGroup(): string
    {
        return __('navigation.setting_management');
    }

    public static function getNavigationLabel(): string
    {
        return __('page.configuration');
    }
}
