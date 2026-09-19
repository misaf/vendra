<?php

declare(strict_types=1);

namespace App\Settings\Tasks;

use Spatie\LaravelSettings\SettingsContainer;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

final class SwitchSettingsTask implements SwitchTenantTask
{
    public function makeCurrent(IsTenant $tenant): void
    {
        $this->forgetResolvedSettings();
    }

    public function forgetCurrent(): void
    {
        $this->forgetResolvedSettings();
    }

    private function forgetResolvedSettings(): void
    {
        foreach (resolve(SettingsContainer::class)->getSettingClasses() as $settingsClass) {
            if (is_string($settingsClass)) {
                app()->forgetInstance($settingsClass);
            }
        }
    }
}
