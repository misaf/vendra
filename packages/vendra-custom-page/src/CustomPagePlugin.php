<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPage;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class CustomPagePlugin implements Plugin
{
    use ResolvesPluginInstances;

    public const string ID = 'vendra-custom-page';

    public function getId(): string
    {
        return self::ID;
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Clusters/Resources',
            for: 'Misaf\\VendraCustomPage\\Filament\\Clusters\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
