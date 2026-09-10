<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\Facades\Config;
use Misaf\VendraAffiliate\Filament\Widgets\AffiliateOverviewWidget;
use Misaf\VendraAffiliate\Filament\Widgets\UserAffiliateOverviewWidget;
use Misaf\VendraSupport\Filament\Concerns\HasPluginNavigationGroup;
use Misaf\VendraSupport\Filament\Concerns\ResolvesPluginInstances;

final class AffiliatePlugin implements Plugin
{
    use HasPluginNavigationGroup;
    use ResolvesPluginInstances;

    public const string ID = 'vendra-affiliate';

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
        if ($this->isConfiguredPanel($panel, 'vendra-affiliate.panels')) {
            $panel->discoverResources(
                in: __DIR__.'/Filament/Clusters/Resources',
                for: 'Misaf\\VendraAffiliate\\Filament\\Clusters\\Resources',
            );

            $panel->widgets([
                AffiliateOverviewWidget::class,
            ]);
        }

        if ($this->isConfiguredPanel($panel, 'vendra-affiliate.user_panels')) {
            $panel->widgets([
                UserAffiliateOverviewWidget::class,
            ]);
        }
    }

    public function boot(Panel $panel): void {}

    private function isConfiguredPanel(Panel $panel, string $configKey): bool
    {
        return in_array($panel->getId(), Config::array($configKey), true);
    }
}
