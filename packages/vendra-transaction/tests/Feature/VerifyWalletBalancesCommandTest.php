<?php

declare(strict_types=1);

use Misaf\VendraTransaction\Actions\PostLedgerEntryAction;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('passes when cached balances match the ledger', function (): void {
    $wallet = WalletFactory::new()->create();
    resolve(PostLedgerEntryAction::class)->execute($wallet, 2_500);

    artisan('vendra-transaction:verify-balances')->assertSuccessful();
});

it('fails on drifted balances and repairs them on demand', function (): void {
    $wallet = WalletFactory::new()->create();
    resolve(PostLedgerEntryAction::class)->execute($wallet, 2_500);

    $wallet->newQuery()->whereKey($wallet->id)->toBase()->update(['balance' => 9_999]);

    artisan('vendra-transaction:verify-balances')->assertFailed();
    artisan('vendra-transaction:verify-balances', ['--repair' => true])->assertSuccessful();

    expect($wallet->fresh()->balance)->toBe(2_500);
});
