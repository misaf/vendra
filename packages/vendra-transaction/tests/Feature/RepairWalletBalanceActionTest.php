<?php

declare(strict_types=1);

use Misaf\VendraTransaction\Actions\PostLedgerEntryAction;
use Misaf\VendraTransaction\Actions\RepairWalletBalanceAction;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Models\Wallet;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('repairs the balance from the ledger as it is when the lock is taken', function (): void {
    $wallet = WalletFactory::new()->create();
    resolve(PostLedgerEntryAction::class)->execute($wallet, 2_500);

    $staleWallet = Wallet::query()->findOrFail($wallet->id);

    resolve(PostLedgerEntryAction::class)->execute($wallet, 500);
    $wallet->newQuery()->whereKey($wallet->id)->toBase()->update(['balance' => 9_999]);

    $repairedBalance = resolve(RepairWalletBalanceAction::class)->execute($staleWallet);

    expect($repairedBalance)->toBe(3_000)
        ->and($wallet->fresh()->balance)->toBe(3_000);
});
