<?php

declare(strict_types=1);

return [
    'operator' => [
        'username' => env('CONSOLE_OPERATOR_USERNAME', ''),
        'email' => env('CONSOLE_OPERATOR_EMAIL', ''),
        'password' => env('CONSOLE_OPERATOR_PASSWORD', ''),
    ],

    /*
     | Deployment-level console settings. Everything here is scoped to the
     | console panel and fixed for the deployment; a rule the reseller or store
     | layer would have to honour cannot live here, because those packages sit
     | below the console and cannot read it. Operator-editable platform rules —
     | store creation, for one — are settings rows instead, owned by the layer
     | that enforces them.
     */
    'platform' => [
        // The console panel's brand name.
        'name' => env('CONSOLE_PLATFORM_NAME', 'Vendra Console'),
    ],
];
