<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Misaf\VendraSupport\Filament\Concerns\HasPluginNavigationGroup;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class NewsletterPlugin implements Plugin
{
    use HasPluginNavigationGroup;
    use ResolvesPluginInstances;

    public const string ID = 'vendra-newsletter';

    public function getId(): string
    {
        return self::ID;
    }

    protected function defaultNavigationGroup(): string
    {
        return 'vendra-support::navigation.groups.Marketing';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverResources(
            in: __DIR__.'/Filament/Clusters/Resources',
            for: 'Misaf\\VendraNewsletter\\Filament\\Clusters\\Resources',
        );
    }

    public function boot(Panel $panel): void {}
}
