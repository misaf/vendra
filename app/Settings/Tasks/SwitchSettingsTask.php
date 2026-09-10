<?php

declare(strict_types=1);

namespace App\Settings\Tasks;

use Spatie\LaravelSettings\SettingsContainer;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\Tasks\SwitchTenantTask;

/**
 * Drops resolved settings objects when the current tenant changes.
 *
 * Settings classes are bound as scoped instances, so the first store to read
 * one during a request would otherwise hand its values to every store switched
 * to afterwards — in a queue worker or an `eachTenant()` loop, that is a
 * cross-tenant leak rather than a stale read.
 */
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
