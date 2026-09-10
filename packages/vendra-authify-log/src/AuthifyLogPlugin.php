<?php

declare(strict_types=1);

namespace Misaf\VendraAuthifyLog;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class AuthifyLogPlugin implements Plugin
{
    use ResolvesPluginInstances;

    public const string ID = 'vendra-authify-log';

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void
    {
        $panel
            ->discoverPages(
                in: __DIR__.'/Filament/Pages',
                for: 'Misaf\\VendraAuthifyLog\\Filament\\Pages',
            )
            ->discoverResources(
                in: __DIR__.'/Filament/Clusters/Resources',
                for: 'Misaf\\VendraAuthifyLog\\Filament\\Clusters\\Resources',
            )
            ->discoverWidgets(
                in: __DIR__.'/Filament/Widgets',
                for: 'Misaf\\VendraAuthifyLog\\Filament\\Widgets',
            );
    }

    public function boot(Panel $panel): void {}
}
