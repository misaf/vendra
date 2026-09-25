<?php

declare(strict_types=1);

use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateReferralFactory;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Listeners\TransactionCommissionSubscriber;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraTransaction\Actions\ApproveTransactionAction;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Events\TransactionApproved;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraUser\Models\User;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

function referredDeposit(int $amount, int $commissionPercent = 20): Transaction
{
    $affiliate = AffiliateFactory::new()
        ->active()
        ->state(['commission_percent' => $commissionPercent])
        ->create();

    $user = User::factory()->create();

    AffiliateReferralFactory::new()
        ->forAffiliate($affiliate)
        ->forUser($user)
        ->create();

    return TransactionFactory::new()
        ->forWallet(WalletFactory::new()->forUser($user)->create())
        ->deposit()
        ->pending()
        ->create(['amount' => $amount]);
}

it('credits a commission when a referred deposit is approved', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', true);

    $transaction = referredDeposit(amount: 10_000, commissionPercent: 20);

    resolve(ApproveTransactionAction::class)->execute($transaction);

    $commission = AffiliateCommission::query()->sole();

    expect($commission->conversion_type)->toBe(ConversionTypeEnum::Deposit)
        ->and($commission->amount)->toBe(2_000)
        ->and($commission->status)->toBe(CommissionStatusEnum::Approved)
        ->and($commission->source_id)->toBe($transaction->id);
});

it('credits a repeated event only once', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', true);

    $transaction = referredDeposit(amount: 10_000);
    $subscriber = resolve(TransactionCommissionSubscriber::class);

    $subscriber->transactionApproved(new TransactionApproved($transaction));
    $subscriber->transactionApproved(new TransactionApproved($transaction));

    expect(AffiliateCommission::query()->count())->toBe(1);
});

it('queues nothing when a deposit is updated without being approved', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', true);
    $transaction = referredDeposit(amount: 10_000);
    Queue::fake();

    $transaction->markProcessing();
    $transaction->update(['amount' => 12_000]);

    Queue::assertNotPushed(CallQueuedListener::class, fn (CallQueuedListener $job): bool => $job->class === TransactionCommissionSubscriber::class);
});

it('ignores deposits when the deposit conversion is disabled', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', false);

    $transaction = referredDeposit(amount: 10_000);

    resolve(ApproveTransactionAction::class)->execute($transaction);

    expect(AffiliateCommission::query()->count())->toBe(0);
});

it('ignores deposits from users without a referral', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', true);

    $transaction = TransactionFactory::new()
        ->deposit()
        ->pending()
        ->create(['amount' => 10_000]);

    resolve(ApproveTransactionAction::class)->execute($transaction);

    expect(AffiliateCommission::query()->count())->toBe(0);
});

it('credits a deposit whose wallet was deleted before the queued listener ran', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', true);

    $transaction = referredDeposit(amount: 10_000, commissionPercent: 20);
    $transaction->wallet->delete();

    resolve(TransactionCommissionSubscriber::class)->transactionApproved(new TransactionApproved($transaction->fresh()));

    expect(AffiliateCommission::query()->sole()->amount)->toBe(2_000);
});

it('does not queue a commission for a platform ledger deposit', function (): void {
    Queue::fake();
    $transaction = referredDeposit(amount: 10_000);
    $transaction->forceFill(['tenant_id' => null])->save();

    event(new TransactionApproved($transaction->refresh()));

    Queue::assertNotPushed(CallQueuedListener::class, fn (CallQueuedListener $job): bool => $job->class === TransactionCommissionSubscriber::class);
});
