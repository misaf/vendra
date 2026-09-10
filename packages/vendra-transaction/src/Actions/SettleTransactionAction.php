<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\Transaction;

/**
 * Settles an approved transaction into the ledger: the signed principal
 * against the source wallet, the mirrored credit on the counterparty wallet
 * for transfers, and any fee as its own negative entry.
 */
final class SettleTransactionAction
{
    public function __construct(private readonly PostLedgerEntryAction $postLedgerEntry) {}

    public function execute(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $transaction->loadMissing(['wallet', 'counterpartyWallet', 'transactionFee']);

            $signedAmount = $transaction->transaction_type->ledgerSign() * $transaction->amount;

            $this->postLedgerEntry->execute($transaction->wallet, $signedAmount, $transaction);

            if ($transaction->transaction_type === TransactionTypeEnum::Transfer && $transaction->counterpartyWallet !== null) {
                $this->postLedgerEntry->execute($transaction->counterpartyWallet, $transaction->amount, $transaction);
            }

            if ($transaction->transactionFee !== null && $transaction->transactionFee->amount > 0) {
                $this->postLedgerEntry->execute($transaction->wallet, -$transaction->transactionFee->amount, $transaction->transactionFee);
            }
        });
    }
}
