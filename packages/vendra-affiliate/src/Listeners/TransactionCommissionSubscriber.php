<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Misaf\VendraAffiliate\Actions\CreditCommissionAction;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Events\TransactionApproved;

/**
 * Credit a commission for a referred user's approved deposit.
 *
 * Approval is final, so a credited commission is never reversed from here.
 */
final class TransactionCommissionSubscriber implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public function __construct(
        private readonly CreditCommissionAction $creditCommission,
    ) {}

    public function transactionApproved(TransactionApproved $event): void
    {
        $transaction = $event->transaction;

        if ($transaction->transaction_type !== TransactionTypeEnum::Deposit) {
            return;
        }

        if (! ConversionTypeEnum::Deposit->isEnabled()) {
            return;
        }

        $transaction->loadMissing('wallet');

        $referral = AffiliateReferral::forUser($transaction->wallet->user_id);
        $affiliate = $referral?->affiliate;

        if (! $affiliate instanceof Affiliate) {
            return;
        }

        $this->creditCommission->execute(
            affiliate: $affiliate,
            conversionType: ConversionTypeEnum::Deposit,
            amount: $affiliate->commissionFor(abs($transaction->amount)),
            source: $transaction,
            referral: $referral,
        );
    }

    /**
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            TransactionApproved::class => 'transactionApproved',
        ];
    }
}
