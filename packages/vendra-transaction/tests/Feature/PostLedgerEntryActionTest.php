<?php

declare(strict_types=1);

use Misaf\VendraTransaction\Actions\PostLedgerEntryAction;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Exceptions\InsufficientBalanceException;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('posts entries and keeps the cached wallet balance in lockstep', function (): void {
    $wallet = WalletFactory::new()->create();
    $ledger = resolve(PostLedgerEntryAction::class);

    $first = $ledger->execute($wallet, 5_000);
    $second = $ledger->execute($wallet, -2_000);

    expect($first->balance_after)->toBe(5_000)
        ->and($second->balance_after)->toBe(3_000)
        ->and($wallet->balance)->toBe(3_000)
        ->and($wallet->fresh()->balance)->toBe(3_000)
        ->and($wallet->ledgerEntries()->count())->toBe(2)
        ->and((int) $wallet->ledgerEntries()->sum('amount'))->toBe(3_000);
});

it('rejects entries that would drive the balance negative', function (): void {
    $wallet = WalletFactory::new()->create();
    $ledger = resolve(PostLedgerEntryAction::class);

    $ledger->execute($wallet, 1_000);

    expect(fn () => $ledger->execute($wallet, -1_001))
        ->toThrow(InsufficientBalanceException::class)
        ->and($wallet->fresh()->balance)->toBe(1_000)
        ->and($wallet->ledgerEntries()->count())->toBe(1);
});

it('refuses to mutate or delete posted ledger entries', function (): void {
    $wallet = WalletFactory::new()->create();
    $entry = resolve(PostLedgerEntryAction::class)->execute($wallet, 1_000);

    expect(fn () => $entry->update(['amount' => 9_999]))->toThrow(RuntimeException::class)
        ->and(fn () => $entry->delete())->toThrow(RuntimeException::class);
});
