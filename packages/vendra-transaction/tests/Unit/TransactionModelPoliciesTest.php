<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Misaf\VendraTransaction\Enums\TransactionGatewayPolicyEnum;
use Misaf\VendraTransaction\Enums\TransactionPolicyEnum;
use Misaf\VendraTransaction\Enums\WalletPolicyEnum;
use Misaf\VendraTransaction\Models\LedgerEntry;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\Models\TransactionGateway;
use Misaf\VendraTransaction\Models\Wallet;

it('defines the expected transaction models', function (): void {
    expect((new Transaction)->getFillable())->toContain('wallet_id', 'transaction_gateway_id', 'counterparty_wallet_id', 'transaction_type', 'token', 'idempotency_key', 'amount', 'status')
        ->and((new TransactionGateway)->getFillable())->toContain('name', 'description', 'slug', 'position', 'active', 'is_default')
        ->and((new Wallet)->getFillable())->toContain('user_id', 'currency_code')
        ->and((new Wallet)->getFillable())->not->toContain('balance')
        ->and((new LedgerEntry)->getFillable())->toContain('amount', 'balance_after')
        ->and((new Transaction)->getHidden())->toContain('tenant_id', 'idempotency_key')
        ->and((new Wallet)->getHidden())->toContain('tenant_id');
});

it('defines the expected transaction relationships', function (): void {
    expect(new ReflectionMethod(Transaction::class, 'wallet')->getReturnType()?->getName())->toBe(BelongsTo::class)
        ->and(new ReflectionMethod(Transaction::class, 'counterpartyWallet')->getReturnType()?->getName())->toBe(BelongsTo::class)
        ->and(new ReflectionMethod(Transaction::class, 'transactionGateway')->getReturnType()?->getName())->toBe(BelongsTo::class)
        ->and(new ReflectionMethod(Transaction::class, 'transactionFee')->getReturnType()?->getName())->toBe(HasOne::class)
        ->and(new ReflectionMethod(Transaction::class, 'transactionMetadatas')->getReturnType()?->getName())->toBe(HasMany::class)
        ->and(new ReflectionMethod(Transaction::class, 'ledgerEntries')->getReturnType()?->getName())->toBe(MorphMany::class)
        ->and(new ReflectionMethod(Wallet::class, 'user')->getReturnType()?->getName())->toBe(BelongsTo::class)
        ->and(new ReflectionMethod(Wallet::class, 'ledgerEntries')->getReturnType()?->getName())->toBe(HasMany::class)
        ->and(new ReflectionMethod(LedgerEntry::class, 'wallet')->getReturnType()?->getName())->toBe(BelongsTo::class)
        ->and(new ReflectionMethod(LedgerEntry::class, 'source')->getReturnType()?->getName())->toBe(MorphTo::class);
});

it('defines policy permissions for all transaction resources', function (): void {
    expect(array_column(TransactionPolicyEnum::cases(), 'value'))->toBe([
        'create-transaction',
        'delete-transaction',
        'delete-any-transaction',
        'force-delete-transaction',
        'force-delete-any-transaction',
        'restore-transaction',
        'restore-any-transaction',
        'update-transaction',
        'view-transaction',
        'view-any-transaction',
    ])
        ->and(array_column(TransactionGatewayPolicyEnum::cases(), 'value'))->toBe([
            'create-transaction-gateway',
            'delete-transaction-gateway',
            'delete-any-transaction-gateway',
            'force-delete-transaction-gateway',
            'force-delete-any-transaction-gateway',
            'reorder-transaction-gateway',
            'restore-transaction-gateway',
            'restore-any-transaction-gateway',
            'update-transaction-gateway',
            'view-transaction-gateway',
            'view-any-transaction-gateway',
        ])
        ->and(array_column(WalletPolicyEnum::cases(), 'value'))->toBe([
            'create-wallet',
            'delete-wallet',
            'delete-any-wallet',
            'force-delete-wallet',
            'force-delete-any-wallet',
            'restore-wallet',
            'restore-any-wallet',
            'update-wallet',
            'view-wallet',
            'view-any-wallet',
        ]);
});
