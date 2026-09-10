<?php

declare(strict_types=1);

use Misaf\VendraAffiliate\Database\Factories\AffiliateFactory;
use Misaf\VendraAffiliate\Database\Factories\AffiliateReferralFactory;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Listeners\TransactionCommissionSubscriber;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraTransaction\Database\Factories\TransactionFactory;
use Misaf\VendraTransaction\Database\Factories\WalletFactory;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\States\Declined;
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
        ->approved()
        ->create(['amount' => $amount]);
}

it('credits a commission when a referred deposit is approved', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', true);

    $transaction = referredDeposit(amount: 10_000, commissionPercent: 20);

    resolve(TransactionCommissionSubscriber::class)->transactionUpdated($transaction);

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

    $subscriber->transactionUpdated($transaction);
    $subscriber->transactionUpdated($transaction);

    expect(AffiliateCommission::query()->count())->toBe(1);
});

it('reverses the unpaid commission when the deposit leaves the approved state', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', true);

    $transaction = referredDeposit(amount: 10_000);
    $subscriber = resolve(TransactionCommissionSubscriber::class);

    $subscriber->transactionUpdated($transaction);

    $transaction->update(['status' => Declined::class]);
    $subscriber->transactionUpdated($transaction->refresh());

    expect(AffiliateCommission::query()->sole()->status)->toBe(CommissionStatusEnum::Reversed);
});

it('ignores deposits when the deposit conversion is disabled', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', false);

    $transaction = referredDeposit(amount: 10_000);

    resolve(TransactionCommissionSubscriber::class)->transactionUpdated($transaction);

    expect(AffiliateCommission::query()->count())->toBe(0);
});

it('ignores deposits from users without a referral', function (): void {
    config()->set('vendra-affiliate.conversions.deposit.enabled', true);

    $transaction = TransactionFactory::new()
        ->deposit()
        ->approved()
        ->create(['amount' => 10_000]);

    resolve(TransactionCommissionSubscriber::class)->transactionUpdated($transaction);

    expect(AffiliateCommission::query()->count())->toBe(0);
});
