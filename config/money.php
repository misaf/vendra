<?php

declare(strict_types=1);

return [
    /*
     |--------------------------------------------------------------------------
     | Laravel money
     |--------------------------------------------------------------------------
     */
    'locale'            => config('app.locale', 'en_US'),
    'defaultCurrency'   => config('app.currency', 'USD'),
    'defaultFormatter'  => null,
    'defaultSerializer' => null,
    'isoCurrenciesPath' => is_dir(__DIR__ . '/../vendor')
        ? __DIR__ . '/../vendor/moneyphp/money/resources/currency.php'
        : __DIR__ . '/../../../moneyphp/money/resources/currency.php',
    'currencies' => [
        'iso'     => 'all',
        'bitcoin' => 'all',
        'custom'  => [
            'IRR' => 0,
            'IRT' => 0,
            'BTC' => 8,
            'ETH' => 8,
        ],
    ],
];
