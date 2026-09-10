<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Misaf\VendraTransaction\Actions\PostLedgerEntryAction;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Events\TransactionApproved;
use Misaf\VendraTransaction\Events\TransactionDeclined;
use Misaf\VendraTransaction\Events\TransactionFailed;
use Misaf\VendraTransaction\Exceptions\InsufficientBalanceException;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\States\Approved;
use Misaf\VendraTransaction\States\Declined;
use Misaf\VendraTransaction\States\Pending;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('starts pending and settles a deposit into the ledger on approval', function (): void {
    Event::fake([TransactionApproved::class]);

    $wallet = WalletFactory::new()->create();
    $transaction = TransactionFactory::new()->forWallet($wallet)->deposit()->create(['amount' => 4_000]);

    expect($transaction->status)->toBeInstanceOf(Pending::class);

    $transaction->approve();

    expect($transaction->fresh()->status)->toBeInstanceOf(Approved::class)
        ->and($wallet->fresh()->balance)->toBe(4_000)
        ->and($transaction->ledgerEntries()->count())->toBe(1);

    Event::assertDispatched(TransactionApproved::class);
});

it('settles a withdrawal as a negative ledger entry', function (): void {
    $wallet = WalletFactory::new()->create();
    resolve(PostLedgerEntryAction::class)->execute($wallet, 10_000);

    $transaction = TransactionFactory::new()->forWallet($wallet)->withdrawal()->create(['amount' => 3_000]);
    $transaction->approve();

    expect($wallet->fresh()->balance)->toBe(7_000)
        ->and($transaction->ledgerEntries()->first()->amount)->toBe(-3_000);
});

it('refuses to settle a withdrawal beyond the wallet balance and stays pending', function (): void {
    $wallet = WalletFactory::new()->create();
    resolve(PostLedgerEntryAction::class)->execute($wallet, 1_000);

    $transaction = TransactionFactory::new()->forWallet($wallet)->withdrawal()->create(['amount' => 2_000]);

    expect(fn () => $transaction->approve())->toThrow(InsufficientBalanceException::class)
        ->and($transaction->fresh()->status)->toBeInstanceOf(Pending::class)
        ->and($wallet->fresh()->balance)->toBe(1_000);
});

it('settles a transfer against both wallets atomically', function (): void {
    $source = WalletFactory::new()->create();
    $destination = WalletFactory::new()->create();
    resolve(PostLedgerEntryAction::class)->execute($source, 5_000);

    $transaction = TransactionFactory::new()
        ->forWallet($source)
        ->transfer()
        ->create([
            'amount' => 2_000,
            'counterparty_wallet_id' => $destination->id,
        ]);

    $transaction->approve();

    expect($source->fresh()->balance)->toBe(3_000)
        ->and($destination->fresh()->balance)->toBe(2_000)
        ->and($transaction->ledgerEntries()->count())->toBe(2);
});

it('settles the fee as its own negative ledger entry', function (): void {
    $wallet = WalletFactory::new()->create();
    $transaction = TransactionFactory::new()->forWallet($wallet)->deposit()->create(['amount' => 4_000]);
    $transaction->transactionFee()->create(['amount' => 500]);

    $transaction->approve();

    expect($wallet->fresh()->balance)->toBe(3_500)
        ->and($wallet->ledgerEntries()->count())->toBe(2)
        ->and((int) $wallet->ledgerEntries()->min('amount'))->toBe(-500);
});

it('dispatches events on decline and fail', function (): void {
    Event::fake([TransactionDeclined::class, TransactionFailed::class]);

    TransactionFactory::new()->deposit()->create()->decline();
    TransactionFactory::new()->deposit()->create()->fail();

    Event::assertDispatched(TransactionDeclined::class);
    Event::assertDispatched(TransactionFailed::class);
});

it('forbids transitions out of terminal states', function (): void {
    $transaction = TransactionFactory::new()->deposit()->create();
    $transaction->decline();

    expect($transaction->fresh()->status)->toBeInstanceOf(Declined::class)
        ->and(fn () => $transaction->approve())->toThrow(TransitionNotFound::class);
});

it('marks processing and review as intermediate states', function (): void {
    $transaction = TransactionFactory::new()->deposit()->create();

    $transaction->markProcessing();
    $transaction->markReview();

    expect($transaction->fresh()->status->isFinal())->toBeFalse();

    $transaction->approve();

    expect($transaction->fresh()->status->isFinal())->toBeTrue();
});

it('generates a unique numeric token on creation', function (): void {
    $transaction = TransactionFactory::new()->deposit()->create();

    expect($transaction->token)->toMatch('/^[1-9]{20}$/');
});

it('scopes transactions by type and state', function (): void {
    $wallet = WalletFactory::new()->create();
    TransactionFactory::new()->forWallet($wallet)->deposit()->create()->approve();
    TransactionFactory::new()->forWallet($wallet)->bonus()->create();

    expect(Transaction::query()->deposit()->count())->toBe(1)
        ->and(Transaction::query()->approved()->count())->toBe(1)
        ->and(Transaction::query()->pending()->count())->toBe(1)
        ->and(Transaction::query()->bonus()->pending()->count())->toBe(1);
});
