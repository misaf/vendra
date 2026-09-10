<?php

declare(strict_types=1);

namespace Misaf\VendraMultimedia;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class MultimediaPlugin implements Plugin
{
    use ResolvesPluginInstances;

    public const string ID = 'vendra-multimedia';

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverResources(
                in: __DIR__.'/Filament/Clusters/Resources',
                for: 'Misaf\\VendraMultimedia\\Filament\\Clusters\\Resources',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Misaf\\VendraMultimedia\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void {}
}
