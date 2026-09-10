<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Misaf\VendraTransaction\Models\Wallet;

/**
 * Recomputes every wallet balance from its immutable ledger entries and
 * reports wallets whose cached balance has drifted. With --repair the
 * cached balance is reset to the ledger-derived value.
 */
#[Description('Verify cached wallet balances against the ledger')]
#[Signature('vendra-transaction:verify-balances {--repair : Reset drifted cached balances to the ledger-derived value}')]
final class VerifyWalletBalancesCommand extends Command
{
    public function handle(): int
    {
        $drifted = 0;

        Wallet::query()
            ->withTrashed()
            ->withSum('ledgerEntries as ledger_balance', 'amount')
            ->chunkById(500, function ($wallets) use (&$drifted): void {
                foreach ($wallets as $wallet) {
                    $ledgerBalance = (int) ($wallet->getAttribute('ledger_balance') ?? 0);

                    if ($ledgerBalance === $wallet->balance) {
                        continue;
                    }

                    $drifted++;
                    $this->error("Wallet [{$wallet->id}] cached balance [{$wallet->balance}] differs from ledger balance [{$ledgerBalance}].");

                    if ($this->option('repair')) {
                        $wallet->forceFill(['balance' => $ledgerBalance])->save();
                        $this->info("Wallet [{$wallet->id}] balance repaired to [{$ledgerBalance}].");
                    }
                }
            });

        if ($drifted === 0) {
            $this->info('All wallet balances match the ledger.');

            return self::SUCCESS;
        }

        return $this->option('repair') ? self::SUCCESS : self::FAILURE;
    }
}
