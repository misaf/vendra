<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Listeners;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Misaf\VendraAffiliate\Actions\CreditCommissionAction;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\ConversionTypeEnum;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraAffiliate\Models\AffiliateReferral;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\States\Approved;

/**
 * Reconciles deposit transactions of referred users with the commission
 * ledger: an approved deposit credits a commission (idempotently), and a
 * deposit that leaves the approved state reverses its unpaid commission.
 */
final class TransactionCommissionSubscriber implements ShouldQueueAfterCommit
{
    use InteractsWithQueue;

    public function __construct(
        private readonly CreditCommissionAction $creditCommission,
    ) {}

    public function transactionUpdated(Transaction $transaction): void
    {
        if ($transaction->transaction_type !== TransactionTypeEnum::Deposit) {
            return;
        }

        if (! ConversionTypeEnum::Deposit->isEnabled()) {
            return;
        }

        if ($transaction->status instanceof Approved) {
            $this->creditDeposit($transaction);

            return;
        }

        $this->reverseDeposit($transaction);
    }

    /**
     * @return array<string, string>
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            'eloquent.updated: '.Transaction::class => 'transactionUpdated',
        ];
    }

    private function creditDeposit(Transaction $transaction): void
    {
        $transaction->loadMissing('wallet');

        $referral = AffiliateReferral::with('affiliate')
            ->where('user_id', $transaction->wallet->user_id)
            ->first();

        if (! $referral instanceof AffiliateReferral || $referral->affiliate === null) {
            return;
        }

        $affiliate = $referral->affiliate;

        $this->creditCommission->execute(
            affiliate: $affiliate,
            conversionType: ConversionTypeEnum::Deposit,
            amount: intdiv(abs($transaction->amount) * $affiliate->commission_percent, 100),
            source: $transaction,
            referral: $referral,
        );
    }

    private function reverseDeposit(Transaction $transaction): void
    {
        AffiliateCommission::where('conversion_type', ConversionTypeEnum::Deposit)
            ->where('source_type', $transaction->getMorphClass())
            ->where('source_id', $transaction->getKey())
            ->whereIn('status', [CommissionStatusEnum::Pending, CommissionStatusEnum::Approved])
            ->whereNull('affiliate_payout_id')
            ->update(['status' => CommissionStatusEnum::Reversed]);
    }
}
