<?php

declare(strict_types=1);

namespace Misaf\VendraDelivery;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraDelivery\Filament\Pages\ManageDeliverySettings;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class DeliveryPlugin implements Plugin
{
    use ResolvesPluginInstances;

    public const string ID = 'vendra-delivery';

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Clusters/Resources',
            for: 'Misaf\\VendraDelivery\\Filament\\Clusters\\Resources',
        );

        $panel->pages([
            ManageDeliverySettings::class,
        ]);
    }

    public function boot(Panel $panel): void {}
}
