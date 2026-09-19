<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Models\Wallet;

/**
 * The ledger is summed under the wallet lock that PostLedgerEntryAction takes,
 * so a concurrent posting is never overwritten by a stale total.
 */
final class RepairWalletBalanceAction
{
    public function execute(Wallet $wallet): int
    {
        return DB::transaction(function () use ($wallet): int {
            $locked = $wallet->refreshForUpdate();

            $ledgerBalance = (int) $locked->ledgerEntries()->sum('amount');

            $locked->forceFill(['balance' => $ledgerBalance])->save();

            return $ledgerBalance;
        });
    }
}
