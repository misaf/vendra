<?php

declare(strict_types=1);

namespace Misaf\VendraOrder;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class OrderPlugin implements Plugin
{
    use ResolvesPluginInstances;

    public const string ID = 'vendra-order';

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Clusters/Resources',
            for: 'Misaf\\VendraOrder\\Filament\\Clusters\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
