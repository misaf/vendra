<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Filament\Concerns;

use Illuminate\Support\Facades\Config;

trait ResolvesConfiguredPanels
{
    protected function shouldRegisterOnPanel(string $panelId, string $configKey): bool
    {
        return in_array($panelId, $this->configuredPanelIds($configKey), true);
    }

    /**
     * Get the panel ids from `<configKey>.panels`, then `<configKey>.panel`, then admin.
     *
     * @return array<int, string>
     */
    protected function configuredPanelIds(string $configKey): array
    {
        foreach (["{$configKey}.panels", "{$configKey}.panel"] as $key) {
            $panels = Config::get($key);

            if (is_string($panels)) {
                return [$panels];
            }

            if (is_array($panels)) {
                return array_values(array_filter($panels, is_string(...)));
            }
        }

        return ['admin'];
    }
}
