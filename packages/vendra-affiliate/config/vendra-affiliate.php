<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Filament Panels
    |--------------------------------------------------------------------------
    |
    | These panel IDs control where affiliate management, tenant-wide metrics,
    | and the signed-in user's affiliate widget are registered.
    |
    */

    'panels' => ['admin'],

    'user_panels' => ['user'],

    /*
    |--------------------------------------------------------------------------
    | Navigation Group
    |--------------------------------------------------------------------------
    |
    | This translation key determines the sidebar group that contains the
    | affiliate administration cluster.
    |
    */

    'navigation_group' => 'vendra-support::navigation.groups.Marketing',

    /*
    |--------------------------------------------------------------------------
    | Referral Cookie
    |--------------------------------------------------------------------------
    |
    | The cookie that carries a referral from the redirect to registration.
    | Its lifetime, the redirect target and every commission and payout rule
    | are store settings, edited on the affiliate settings page.
    |
    */

    'attribution' => [
        'cookie_name' => 'vendra_affiliate_ref',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payout Gateway
    |--------------------------------------------------------------------------
    |
    | The slug must identify a gateway registered by vendra-transaction.
    |
    */

    'payout' => [
        'transaction_gateway' => 'internal-transactions',
    ],

];
