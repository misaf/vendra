<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'email' => [
        'webhooks' => [
            'default_provider' => env('EMAIL_WEBHOOKS_DEFAULT_PROVIDER', 'resend'),
            'resend' => [
                'webhook_path' => env('EMAIL_WEBHOOKS_RESEND_PATH', '/emails/webhooks/resend'),
                'webhook_name' => env('EMAIL_WEBHOOKS_RESEND_NAME', 'resend'),
            ],
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI'),
    ],

    'turnstile' => [
        'key' => env('TURNSTILE_KEY'),
        'secret' => env('TURNSTILE_SECRET'),
    ],

    'coinpayments' => [
        'host' => env('COINPAYMENTS_HOST'),
        'key' => env('COINPAYMENTS_KEY'),
        'secret' => env('COINPAYMENTS_SECRET'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
    ],

];
