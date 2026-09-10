<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Exceptions\InsufficientBalanceException;
use Misaf\VendraTransaction\Models\LedgerEntry;
use Misaf\VendraTransaction\Models\Wallet;

/**
 * Posts an immutable ledger entry and keeps the cached wallet balance in
 * lockstep: the entry records the signed amount and the resulting balance,
 * written together with the wallet row under a pessimistic lock.
 */
final class PostLedgerEntryAction
{
    public function execute(Wallet $wallet, int $amount, ?Model $source = null): LedgerEntry
    {
        return DB::transaction(function () use ($wallet, $amount, $source): LedgerEntry {
            /** @var Wallet $locked */
            $locked = Wallet::query()->lockForUpdate()->findOrFail($wallet->getKey());

            $balanceAfter = $locked->balance + $amount;

            if ($balanceAfter < 0) {
                throw InsufficientBalanceException::forWallet($locked->id, $locked->balance, $amount);
            }

            $entry = new LedgerEntry([
                'amount' => $amount,
                'balance_after' => $balanceAfter,
            ]);
            $entry->wallet()->associate($locked);

            if ($source instanceof Model) {
                $entry->source()->associate($source);
            }

            $entry->save();

            $locked->forceFill(['balance' => $balanceAfter])->save();
            $wallet->setAttribute('balance', $balanceAfter);

            return $entry;
        });
    }
}
