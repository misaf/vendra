<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Panels
    |--------------------------------------------------------------------------
    |
    | Here you may specify the Filament panels that the cart administration UI
    | is registered on. The cart navigation only appears within the panels
    | listed here. You may provide a single panel ID or an array of IDs to
    | mount the module across multiple panels.
    |
    | Supported: "admin" (string), ["admin", "vendor"] (array)
    |
    */

    'panels' => ['admin'],

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | This value determines the sidebar navigation group that the Carts
    | cluster is nested under. You may provide a translation key or a literal
    | label, allowing you to file the cart UI alongside a host application's
    | own groups. When left empty, the module's default group is used.
    |
    */

    'navigation_group' => 'vendra-support::navigation.groups.Sales',

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    |
    | The module registers its own scheduled task that prunes expired carts.
    | Disable it here to schedule the "vendra-cart:prune-expired" command
    | yourself, or adjust how often it runs with a standard cron expression.
    |
    */

    'schedule' => [
        'enabled' => env('CART_SCHEDULE_ENABLED', true),
        'cron' => env('CART_SCHEDULE_CRON', '0 0 * * *'),
    ],

];
