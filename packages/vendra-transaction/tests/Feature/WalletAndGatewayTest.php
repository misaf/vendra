<?php

declare(strict_types=1);

use Misaf\VendraSupport\Capabilities\CurrencyIntegration;
use Misaf\VendraTransaction\Actions\CreateTransactionAction;
use Misaf\VendraTransaction\Database\Factories\TransactionGatewayFactory;
use Misaf\VendraTransaction\Database\Factories\TransactionLimitFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Exceptions\TransactionLimitExceededException;
use Misaf\VendraTransaction\Facades\TransactionGatewayRegistry;
use Misaf\VendraTransaction\Facades\WalletResolver;
use Misaf\VendraTransaction\States\Pending;
use Misaf\VendraTransaction\Support\TransactionUsers;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('creates a pending transaction with fee and metadata', function (): void {
    $gateway = TransactionGatewayFactory::new()->active()->create(['slug' => 'shetab']);
    $wallet = WalletFactory::new()->create();

    $transaction = resolve(CreateTransactionAction::class)->execute(
        transactionGateway: 'shetab',
        wallet: $wallet,
        transactionType: TransactionTypeEnum::Deposit,
        amount: 5_000,
        metadata: [
            'reference' => 'abc-123',
            'context' => ['source' => 'test'],
        ],
        fee: 250,
    );

    expect($transaction->status)->toBeInstanceOf(Pending::class)
        ->and($transaction->transaction_gateway_id)->toBe($gateway->id)
        ->and($transaction->amount)->toBe(5_000)
        ->and($transaction->transactionFee->amount)->toBe(250)
        ->and($transaction->transactionMetadatas()->pluck('key_value', 'key_name')->all())->toBe([
            'context' => '{"source":"test"}',
            'reference' => 'abc-123',
        ])
        ->and($wallet->fresh()->balance)->toBe(0);
});

it('returns the original transaction when an idempotency key is retried', function (): void {
    TransactionGatewayFactory::new()->active()->create(['slug' => 'shetab']);
    $wallet = WalletFactory::new()->create();

    $first = resolve(CreateTransactionAction::class)->execute(
        transactionGateway: 'shetab',
        wallet: $wallet,
        transactionType: TransactionTypeEnum::Withdrawal,
        amount: 5_000,
        metadata: ['reference' => 'subscription:123'],
        idempotencyKey: 'subscription:123',
    );
    $retried = resolve(CreateTransactionAction::class)->execute(
        transactionGateway: 'shetab',
        wallet: $wallet,
        transactionType: TransactionTypeEnum::Withdrawal,
        amount: 5_000,
        metadata: ['reference' => 'subscription:123'],
        idempotencyKey: 'subscription:123',
    );

    expect($retried->is($first))->toBeTrue()
        ->and($first->idempotency_key)->toBe('subscription:123')
        ->and($wallet->transactions()->count())->toBe(1)
        ->and($first->transactionMetadatas()->count())->toBe(1);
});

it('rejects an idempotency key reused for different transaction details', function (): void {
    TransactionGatewayFactory::new()->active()->create(['slug' => 'shetab']);
    $wallet = WalletFactory::new()->create();

    resolve(CreateTransactionAction::class)->execute(
        transactionGateway: 'shetab',
        wallet: $wallet,
        transactionType: TransactionTypeEnum::Withdrawal,
        amount: 5_000,
        idempotencyKey: 'subscription:123',
    );

    expect(fn () => resolve(CreateTransactionAction::class)->execute(
        transactionGateway: 'shetab',
        wallet: $wallet,
        transactionType: TransactionTypeEnum::Withdrawal,
        amount: 6_000,
        idempotencyKey: 'subscription:123',
    ))->toThrow(LogicException::class);
});

it('enforces the per-wallet transaction limit at creation', function (): void {
    TransactionGatewayFactory::new()->active()->create(['slug' => 'shetab']);
    $wallet = WalletFactory::new()->create();
    TransactionLimitFactory::new()->forWallet($wallet)->ofType(TransactionTypeEnum::Withdrawal)->create(['amount' => 1_000]);

    expect(fn () => resolve(CreateTransactionAction::class)->execute(
        transactionGateway: 'shetab',
        wallet: $wallet,
        transactionType: TransactionTypeEnum::Withdrawal,
        amount: 1_001,
    ))->toThrow(TransactionLimitExceededException::class);
});

it('resolves gateways by slug and ignores disabled ones', function (): void {
    TransactionGatewayFactory::new()->inactive()->create(['slug' => 'coinpayments']);
    TransactionGatewayFactory::new()->internal()->create();

    expect(TransactionGatewayRegistry::hasActive('coinpayments'))->toBeFalse()
        ->and(TransactionGatewayRegistry::hasActive('internal-transactions'))->toBeTrue()
        ->and(TransactionGatewayRegistry::hasAnyActive())->toBeFalse()
        ->and(fn () => TransactionGatewayRegistry::get('coinpayments'))->toThrow(RuntimeException::class);
});

it('provisions the default wallet in the resolved default currency', function (): void {
    $user = TransactionUsers::model()::factory()->create();

    $wallet = WalletResolver::defaultWalletFor($user);

    expect($wallet->currency_code)->toBe(CurrencyIntegration::defaultCode());
});

it('identifies internal transactions by the internal gateway', function (): void {
    $internal = TransactionGatewayFactory::new()->internal()->create();
    $external = TransactionGatewayFactory::new()->active()->create(['slug' => 'shetab']);
    $wallet = WalletFactory::new()->create();

    $internalTransaction = resolve(CreateTransactionAction::class)->execute(
        transactionGateway: $internal,
        wallet: $wallet,
        transactionType: TransactionTypeEnum::Deposit,
        amount: 1_000,
    );
    $externalTransaction = resolve(CreateTransactionAction::class)->execute(
        transactionGateway: $external,
        wallet: $wallet,
        transactionType: TransactionTypeEnum::Deposit,
        amount: 1_000,
    );

    expect(TransactionGatewayRegistry::isInternal($internalTransaction))->toBeTrue()
        ->and(TransactionGatewayRegistry::isInternal($externalTransaction))->toBeFalse();
});

it('provisions one wallet per user and currency', function (): void {
    $user = TransactionUsers::model()::factory()->create();

    $wallet = WalletResolver::walletFor($user, 'EUR');
    $again = WalletResolver::walletFor($user, 'EUR');

    expect($again->is($wallet))->toBeTrue()
        ->and($wallet->currency_code)->toBe('EUR')
        ->and($user->wallets()->count())->toBe(1);
});
