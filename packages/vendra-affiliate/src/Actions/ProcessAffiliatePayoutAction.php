<?php

declare(strict_types=1);

namespace Misaf\VendraAffiliate\Actions;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Misaf\VendraAffiliate\Enums\CommissionStatusEnum;
use Misaf\VendraAffiliate\Enums\PayoutStatusEnum;
use Misaf\VendraAffiliate\Models\Affiliate;
use Misaf\VendraAffiliate\Models\AffiliateCommission;
use Misaf\VendraAffiliate\Models\AffiliatePayout;
use Misaf\VendraTransaction\Actions\ApproveTransactionAction;
use Misaf\VendraTransaction\Actions\CreateTransactionAction;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Facades\WalletResolver;
use Misaf\VendraUser\Models\User;
use Spatie\QueueableAction\QueueableAction;

/**
 * If the wallet transaction fails, the commissions stay payable.
 */
final class ProcessAffiliatePayoutAction
{
    use QueueableAction;

    public function __construct(
        private readonly CreateTransactionAction $createTransactionAction,
        private readonly ApproveTransactionAction $approveTransactionAction,
    ) {}

    public function execute(Affiliate $affiliate): ?AffiliatePayout
    {
        return DB::transaction(function () use ($affiliate): ?AffiliatePayout {
            $commissions = $affiliate->commissions()
                ->payable()
                ->lockForUpdate()
                ->get();

            $amount = (int) $commissions->sum(fn (AffiliateCommission $commission): int => $commission->amount);

            if ($amount < Config::integer('vendra-affiliate.payout.minimum', 0) || $commissions->count() === 0) {
                return null;
            }

            $affiliate->loadMissing('user');

            if (! $affiliate->user instanceof User) {
                /** @var AffiliatePayout */
                return $affiliate->payouts()->create([
                    'amount' => $amount,
                    'status' => PayoutStatusEnum::Failed,
                ]);
            }

            /** @var AffiliatePayout $payout */
            $payout = $affiliate->payouts()->create([
                'amount' => $amount,
                'status' => PayoutStatusEnum::Pending,
            ]);

            $affiliate->commissions()
                ->whereIn('id', $commissions->modelKeys())
                ->update([
                    'status' => CommissionStatusEnum::Paid,
                    'affiliate_payout_id' => $payout->id,
                ]);

            $transaction = $this->createTransactionAction->execute(
                transactionGateway: Config::string('vendra-affiliate.payout.transaction_gateway', 'internal-transactions'),
                wallet: WalletResolver::firstOrCreateDefaultWalletFor($affiliate->user),
                transactionType: TransactionTypeEnum::Commission,
                amount: $payout->amount,
                metadata: [
                    'type' => 'affiliate-commission',
                    'affiliate_payout_id' => $payout->id,
                ],
            );

            $this->approveTransactionAction->execute($transaction);

            $payout->update([
                'status' => PayoutStatusEnum::Completed,
                'transaction_id' => $transaction->id,
                'processed_at' => now(),
            ]);

            return $payout;
        });
    }
}
