<?php

declare(strict_types=1);

use Misaf\VendraUser\Models\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'console' => [
            'driver' => 'session',
            'provider' => 'console',
        ],

        'reseller' => [
            'driver' => 'session',
            'provider' => 'reseller',
        ],

        'sanctum' => [
            'driver' => 'sanctum',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        /*
        | Console and reseller identities are the same canonical User with a
        | null tenant id, but each panel keeps its own provider so a panel's
        | lookups can be retargeted without moving the other's. Both use the
        | platform-scoped driver, which constrains every lookup to
        | `tenant_id IS NULL`.
        */
        'console' => [
            'driver' => 'platform-eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        'reseller' => [
            'driver' => 'platform-eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        /*
        | Console and reseller each keep their own token store. Reset tokens
        | are keyed by email alone, so any shared table would let one scope
        | overwrite or consume another's token — both against the tenant-facing
        | `users` broker, whose emails may legitimately collide, and against
        | each other, since one platform identity may hold both a console
        | grant and a reseller membership. Expiry and throttle stay at the
        | framework defaults.
        */
        'console' => [
            'provider' => 'console',
            'table' => env('AUTH_CONSOLE_PASSWORD_RESET_TOKEN_TABLE', 'console_password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        'reseller' => [
            'provider' => 'reseller',
            'table' => env('AUTH_RESELLER_PASSWORD_RESET_TOKEN_TABLE', 'reseller_password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the number of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
