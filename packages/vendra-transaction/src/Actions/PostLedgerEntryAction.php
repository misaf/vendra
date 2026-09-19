<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Misaf\VendraTransaction\Exceptions\InsufficientBalanceException;
use Misaf\VendraTransaction\Models\LedgerEntry;
use Misaf\VendraTransaction\Models\Wallet;

/**
 * Post a ledger entry and update the wallet balance under a row lock.
 */
final class PostLedgerEntryAction
{
    public function execute(Wallet $wallet, int $amount, ?Model $source = null): LedgerEntry
    {
        return DB::transaction(function () use ($wallet, $amount, $source): LedgerEntry {
            $locked = $wallet->refreshForUpdate();

            throw_if($locked->trashed(), (new ModelNotFoundException)->setModel(Wallet::class));

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
