<?php

declare(strict_types=1);

use App\Models\SettingsProperty;
use App\Settings\SettingsRepositories\DatabaseSettingsRepository;

return [

    /*
     * Settings will be stored and loaded from these repositories.
     */
    'repositories' => [
        'database' => [
            'type'       => DatabaseSettingsRepository::class,
            'model'      => SettingsProperty::class,
            'table'      => null,
            'connection' => null,
        ],

        'redis' => [
            'type'       => Spatie\LaravelSettings\SettingsRepositories\RedisSettingsRepository::class,
            'connection' => null,
            'prefix'     => null,
        ],
    ],

];
