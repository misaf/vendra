<?php

declare(strict_types=1);

namespace Misaf\VendraCurrency;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class CurrencyPlugin implements Plugin
{
    use ResolvesPluginInstances;

    public const string ID = 'vendra-currency';

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Clusters/Resources',
            for: 'Misaf\\VendraCurrency\\Filament\\Clusters\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
