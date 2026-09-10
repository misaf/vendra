<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Panels
    |--------------------------------------------------------------------------
    |
    | Here you may specify the Filament panels that the delivery administration
    | UI is registered on. The delivery navigation only appears within the
    | panels listed here. You may provide a single panel ID or an array of IDs
    | to mount the module across multiple panels.
    |
    | Supported: "admin" (string), ["admin", "vendor"] (array)
    |
    */

    'panels' => ['admin'],

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    |
    | How far ahead customers may book, and the hour after which same-day
    | delivery closes. Zones, slots, and fees are tenant data managed in the
    | administration UI; only these two calendar rules are configuration.
    |
    */

    'schedule' => [
        'advance_days' => (int) env('DELIVERY_ADVANCE_DAYS', 14),
        'same_day_cutoff_hour' => (int) env('DELIVERY_SAME_DAY_CUTOFF_HOUR', 14),
    ],

];
