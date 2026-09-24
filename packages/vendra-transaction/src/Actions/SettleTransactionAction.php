<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Illuminate\Support\Facades\DB;
use LogicException;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\Models\Wallet;

/**
 * Posts the principal, the counterparty credit for transfers, and any fee.
 */
final readonly class SettleTransactionAction
{
    public function __construct(private PostLedgerEntryAction $postLedgerEntry) {}

    public function execute(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction): void {
            $transaction->loadMissing(['wallet', 'counterpartyWallet', 'transactionFee']);

            $wallet = $transaction->wallet;

            throw_unless($wallet instanceof Wallet, LogicException::class, "Transaction [{$transaction->id}] has no wallet to settle into.");

            $signedAmount = $transaction->transaction_type->ledgerSign() * $transaction->amount;

            $this->postLedgerEntry->execute($wallet, $signedAmount, $transaction);

            if ($transaction->transaction_type === TransactionTypeEnum::Transfer && $transaction->counterpartyWallet !== null) {
                $this->postLedgerEntry->execute($transaction->counterpartyWallet, $transaction->amount, $transaction);
            }

            if ($transaction->transactionFee !== null && $transaction->transactionFee->amount > 0) {
                $this->postLedgerEntry->execute($wallet, -$transaction->transactionFee->amount, $transaction->transactionFee);
            }
        });
    }
}
