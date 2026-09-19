<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS)
    |--------------------------------------------------------------------------
    |
    | Only the canonical API is cross-origin; the panels must not be exposed.
    | The allowed origins are the active storefront domains, which
    | HandleStorefrontCors supplies per request. An empty list fails closed.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    'allowed_origins' => [],

    'allowed_origins_patterns' => [],

    'allowed_headers' => [
        'Accept',
        'Authorization',
        'Content-Type',
        'Origin',
        'X-Requested-With',
        'X-Request-ID',
    ],

    // Echoed back so a storefront can read the correlation id of its own call.
    'exposed_headers' => ['X-Request-ID'],

    'max_age' => 3600,

    // The API is stateless, so storefront calls carry no cookies.
    'supports_credentials' => false,

];
