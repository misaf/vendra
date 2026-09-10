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
    | Affiliate Defaults
    |--------------------------------------------------------------------------
    |
    | These values are applied to newly created affiliates. Monetary amounts
    | are stored as integer minor units, such as cents.
    |
    */

    'defaults' => [
        'commission_percent' => 20,
        'signup_bounty' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Commission Conversions
    |--------------------------------------------------------------------------
    |
    | Each supported conversion type may independently credit commissions.
    |
    */

    'conversions' => [
        'deposit' => ['enabled' => true],
        'signup' => ['enabled' => false],
        'checkout' => ['enabled' => false],
    ],

    /*
    |--------------------------------------------------------------------------
    | Commission Approval
    |--------------------------------------------------------------------------
    |
    | When enabled, credited commissions are immediately payable. Otherwise,
    | they remain pending until approved in the administration panel.
    |
    */

    'commissions' => [
        'auto_approve' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Referral Attribution
    |--------------------------------------------------------------------------
    |
    | These options control the referral cookie and post-attribution redirect.
    |
    */

    'attribution' => [
        'cookie_name' => 'vendra_affiliate_ref',
        'cookie_ttl_days' => 30,
        'redirect_url' => '/',
    ],

    /*
    |--------------------------------------------------------------------------
    | Payout Settlement
    |--------------------------------------------------------------------------
    |
    | The minimum is the smallest payable balance in minor units. The gateway
    | slug must identify a gateway registered by vendra-transaction.
    |
    */

    'payout' => [
        'minimum' => 1000,
        'transaction_gateway' => 'internal-transactions',
    ],

];
