<?php

declare(strict_types=1);

use Misaf\VendraAuthifyLog\Models\AuthifyLog;
use Misaf\VendraAuthifyLog\Notifications\LoginNotification;

return [

    /*
    |--------------------------------------------------------------------------
    | Authify Log Model
    |--------------------------------------------------------------------------
    |
    | This model stores authentication activity captured by the package.
    |
    */

    'model' => AuthifyLog::class,

    /*
    |--------------------------------------------------------------------------
    | Authentication Notifications
    |--------------------------------------------------------------------------
    |
    | These notification classes are associated with Laravel authentication
    | events. Applications may replace individual classes after publishing
    | this configuration file.
    |
    */

    'notifications' => [
        'authenticated' => LoginNotification::class,
        'attempting' => LoginNotification::class,
        'current_device_logout' => LoginNotification::class,
        'failed' => LoginNotification::class,
        'lockout' => LoginNotification::class,
        'other_device_logout' => LoginNotification::class,
        'password_reset' => LoginNotification::class,
        'registered' => LoginNotification::class,
        'logout' => LoginNotification::class,
        'validated' => LoginNotification::class,
        'verified' => LoginNotification::class,
        'login' => LoginNotification::class,
    ],

];
