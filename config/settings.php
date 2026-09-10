<?php

declare(strict_types=1);

use App\Models\SettingsProperty;
use App\Settings\SettingsRepositories\GlobalSettingsRepository;
use App\Settings\SettingsRepositories\TenantSettingsRepository;
use Misaf\VendraStore\Settings\StoreCreationSettings;
use Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository;

return [

    /*
     * Settings classes that live outside `app/Settings` and are therefore not
     * auto-discovered. Package settings classes are registered here.
     */
    'settings' => [
        StoreCreationSettings::class,
    ],

    /*
     * Settings migrations are stored here and run with the ordinary migrations.
     * They seed the platform row every settings class reads before a store has
     * saved anything of its own, so a fresh install never needs manual seeding.
     */
    'migrations_paths' => [
        database_path('settings'),
    ],

    /*
     * Settings belong to a store unless their class says otherwise.
     */
    'default_repository' => 'tenant',

    /*
     * Leave the settings cache off. Its key is the settings class and its
     * prefix is static, so a cached store-scoped class would be served to the
     * next store as well — the repository's scoping happens below the cache.
     */
    'cache' => [
        'enabled' => false,
    ],

    /*
     * Settings will be stored and loaded from these repositories.
     */
    'repositories' => [
        /*
         | Store settings: written into the current store's scope, read with the
         | platform row as the default.
         */
        'tenant' => [
            'type' => TenantSettingsRepository::class,
            'model' => SettingsProperty::class,
            'table' => null,
            'connection' => null,
        ],

        /*
         | Platform settings: one row per property, no tenant. Console and
         | reseller panels run outside tenancy and read only these.
         */
        'global' => [
            'type' => GlobalSettingsRepository::class,
            'model' => SettingsProperty::class,
            'table' => null,
            'connection' => null,
        ],

        'redis' => [
            'type' => RedisSettingsRepository::class,
            'connection' => null,
            'prefix' => null,
        ],
    ],

];
