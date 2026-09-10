<?php

declare(strict_types=1);

namespace Misaf\VendraBlog;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraSupport\Filament\Concerns\HasPluginNavigationGroup;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class BlogPlugin implements Plugin
{
    use HasPluginNavigationGroup;
    use ResolvesPluginInstances;

    public const string ID = 'vendra-blog';

    public function getId(): string
    {
        return self::ID;
    }

    protected function defaultNavigationGroup(): string
    {
        return 'vendra-support::navigation.groups.Content';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Clusters/Resources',
            for: 'Misaf\\VendraBlog\\Filament\\Clusters\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
