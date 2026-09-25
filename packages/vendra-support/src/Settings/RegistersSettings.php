<?php

declare(strict_types=1);

namespace Misaf\VendraSupport\Settings;

use Illuminate\Support\Facades\Config;
use Spatie\LaravelSettings\Settings;
use Spatie\LaravelSettings\SettingsContainer;

trait RegistersSettings
{
    /**
     * Register the package's settings classes and the directory holding their settings migrations.
     *
     * Call it from `packageRegistered()`, before the settings package boots and loads
     * its migration paths. The class list is also what the tenant switch walks to
     * forget resolved settings, so a class left out would leak between stores.
     *
     * @param  list<class-string<Settings>>  $settings
     */
    protected function registerSettings(array $settings, string $migrationsPath): void
    {
        $this->appendToSettingsConfig('settings', $settings);
        $this->appendToSettingsConfig('migrations_paths', [$migrationsPath]);

        $this->app->make(SettingsContainer::class)->clearCache();

        foreach ($settings as $settingsClass) {
            $this->app->scoped($settingsClass, fn (): Settings => new $settingsClass);
        }
    }

    /**
     * @param  list<string>  $values
     */
    private function appendToSettingsConfig(string $key, array $values): void
    {
        $current = array_filter(Config::array('settings.'.$key, []), is_string(...));

        Config::set('settings.'.$key, array_values(array_unique([...$current, ...$values])));
    }
}
