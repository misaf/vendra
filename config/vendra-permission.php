<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Super Admin Role
    |--------------------------------------------------------------------------
    |
    | This role will bypass authorization checks in Gate::after().
    |
    */

    'super_admin_role' => env('VENDRA_PERMISSION_SUPER_ADMIN_ROLE', 'super-admin'),

    /*
    |--------------------------------------------------------------------------
    | Pennant Features
    |--------------------------------------------------------------------------
    |
    | Permission module features are tenant-scoped and resolved through
    | Laravel Pennant.
    |
    */

    'features' => [
        'enabled' => env('VENDRA_PERMISSION_FEATURES_ENABLED', true),

        'discover' => env('VENDRA_PERMISSION_FEATURES_DISCOVER', false),

        'defaults' => [
            'vendra-permission.module-enabled'        => true,
            'vendra-permission.role-management'       => true,
            'vendra-permission.permission-management' => true,
            'vendra-permission.bulk-role-assignment'  => true,
        ],
    ],

];
