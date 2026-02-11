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
        'allowed_domains' => [
            'gmail.com',
            'yahoo.com',
            'yahoo.de',
            'hotmail.com',
            'outlook.com',
            'aol.com',
            'icloud.com',
            'protonmail.com',
            'mail.com',
            'gmx.com',
            'live.com',
            'msn.com',
            'ymail.com',
        ],
        'webhooks' => [
            'default_provider' => env('EMAIL_WEBHOOKS_DEFAULT_PROVIDER', 'resend'),
            'resend'           => [
                'webhook_path' => env('EMAIL_WEBHOOKS_RESEND_PATH', '/emails/webhooks/resend'),
                'webhook_name' => env('EMAIL_WEBHOOKS_RESEND_NAME', 'resend'),
            ],
        ],
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    'turnstile' => [
        'key'    => env('TURNSTILE_KEY'),
        'secret' => env('TURNSTILE_SECRET'),
    ],

    'perfectmoney' => [
        'host'     => env('PERFECTMONEY_HOST'),
        'creation' => [
            'account_id'    => env('PERFECTMONEY_CREATION_ACCOUNT_ID'),
            'passphrase'    => env('PERFECTMONEY_CREATION_PASSPHRASE'),
            'payee_account' => env('PERFECTMONEY_CREATION_PAYEE_ACCOUNT'),
            'payer_account' => env('PERFECTMONEY_CREATION_PAYER_ACCOUNT'),
        ],
        'activation' => [
            'account_id'    => env('PERFECTMONEY_ACTIVATION_ACCOUNT_ID'),
            'passphrase'    => env('PERFECTMONEY_ACTIVATION_PASSPHRASE'),
            'payee_account' => env('PERFECTMONEY_ACTIVATION_PAYEE_ACCOUNT'),
            'payer_account' => env('PERFECTMONEY_ACTIVATION_PAYER_ACCOUNT'),
        ],
    ],

    'coinpayments' => [
        'host'   => env('COINPAYMENTS_HOST'),
        'key'    => env('COINPAYMENTS_KEY'),
        'secret' => env('COINPAYMENTS_SECRET'),
    ],

    'emailable' => [
        'host'    => env('EMAILABLE_HOST'),
        'api_key' => env('EMAILABLE_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'telegram-bot-api' => [
        'token' => env('TELEGRAM_BOT_TOKEN'),
    ],

];
