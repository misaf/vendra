<?php

declare(strict_types=1);

return [
    'description' => [
        'transaction_gateways' => 'Configure payment gateway providers.',
        'transactions' => 'View and process all financial transactions.',
        'wallets' => 'Manage user wallet balances and settings.',
    ],

    'empty_state' => [
        'description' => [
            'transaction_gateways' => 'Add a payment gateway to enable financial transactions.',
            'transactions' => 'Transactions appear when users make deposits, withdrawals, or transfers.',
            'wallets' => 'Wallets are created automatically when users are created.',
        ],

        'heading' => [
            'transaction_gateways' => 'No transaction gateways yet',
            'transactions' => 'No transactions yet',
            'wallets' => 'No wallets yet',
        ],
    ],
];
