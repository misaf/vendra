<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class UserProfilePlugin implements Plugin
{
    use ResolvesPluginInstances;

    public function getId(): string
    {
        return 'vendra-user-profile';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Clusters/Resources',
            for: 'Misaf\\VendraUserProfile\\Filament\\Clusters\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
