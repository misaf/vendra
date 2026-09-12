<?php

declare(strict_types=1);

use Misaf\VendraPermission\Enums\RoleEnum;

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Role
    |--------------------------------------------------------------------------
    |
    | This role will bypass authorization checks in Gate::after(). It is the
    | top-level role granted to a tenant administrator; admins may create
    | additional scoped roles themselves.
    |
    */

    'admin_role' => env('VENDRA_PERMISSION_ADMIN_ROLE', RoleEnum::Admin->value),

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

        'defaults' => [
            'vendra-permission.module-enabled' => true,
            'vendra-permission.role-management' => true,
            'vendra-permission.permission-management' => true,
            'vendra-permission.bulk-role-assignment' => true,
        ],
    ],

];
