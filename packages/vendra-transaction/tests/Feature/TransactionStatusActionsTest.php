<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Misaf\VendraTransaction\Actions\ApproveTransactionAction;
use Misaf\VendraTransaction\Actions\DeclineTransactionAction;
use Misaf\VendraTransaction\Actions\FailTransactionAction;
use Misaf\VendraTransaction\Actions\PostLedgerEntryAction;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Events\TransactionDeclined;
use Misaf\VendraTransaction\Events\TransactionFailed;
use Misaf\VendraTransaction\Exceptions\InsufficientBalanceException;
use Misaf\VendraTransaction\States\Approved;
use Misaf\VendraTransaction\States\Declined;
use Misaf\VendraTransaction\States\Failed;
use Misaf\VendraTransaction\States\Pending;
use Spatie\ModelStates\Exceptions\TransitionNotFound;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('approves a deposit and settles the ledger through the domain action', function (): void {
    $wallet = WalletFactory::new()->create();
    $transaction = TransactionFactory::new()->forWallet($wallet)->deposit()->create(['amount' => 4_000]);

    resolve(ApproveTransactionAction::class)->execute($transaction);

    expect($transaction->fresh()?->status)->toBeInstanceOf(Approved::class)
        ->and($wallet->fresh()->balance)->toBe(4_000)
        ->and($transaction->ledgerEntries()->count())->toBe(1);
});

it('refuses to approve a withdrawal beyond the wallet balance and stays pending', function (): void {
    $wallet = WalletFactory::new()->create();
    resolve(PostLedgerEntryAction::class)->execute($wallet, 1_000);

    $transaction = TransactionFactory::new()->forWallet($wallet)->withdrawal()->create(['amount' => 2_000]);

    expect(fn (): mixed => resolve(ApproveTransactionAction::class)->execute($transaction))
        ->toThrow(InsufficientBalanceException::class)
        ->and($transaction->fresh()?->status)->toBeInstanceOf(Pending::class)
        ->and($wallet->fresh()->balance)->toBe(1_000);
});

it('declines and fails transactions through the domain actions', function (): void {
    Event::fake([TransactionDeclined::class, TransactionFailed::class]);

    $declined = TransactionFactory::new()->deposit()->create();
    resolve(DeclineTransactionAction::class)->execute($declined);

    $failed = TransactionFactory::new()->deposit()->create();
    resolve(FailTransactionAction::class)->execute($failed);

    expect($declined->fresh()?->status)->toBeInstanceOf(Declined::class)
        ->and($failed->fresh()?->status)->toBeInstanceOf(Failed::class);

    Event::assertDispatched(TransactionDeclined::class);
    Event::assertDispatched(TransactionFailed::class);
});

it('forbids approving out of a terminal state', function (): void {
    $transaction = TransactionFactory::new()->deposit()->create();
    resolve(DeclineTransactionAction::class)->execute($transaction);

    expect(fn (): mixed => resolve(ApproveTransactionAction::class)->execute($transaction))
        ->toThrow(TransitionNotFound::class);
});
