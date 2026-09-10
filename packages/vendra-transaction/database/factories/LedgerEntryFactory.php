<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraTransaction\Models\LedgerEntry;
use Misaf\VendraTransaction\Models\Wallet;

/** @extends Factory<LedgerEntry> */
#[UseModel(LedgerEntry::class)]
final class LedgerEntryFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->numberBetween(1, 100_000);

        return [
            'wallet_id' => Wallet::factory(),
            'amount' => $amount,
            'balance_after' => $amount,
        ];
    }

    public function forWallet(Wallet|int $wallet): static
    {
        return $this->state(fn (): array => [
            'wallet_id' => $wallet instanceof Wallet ? $wallet->id : $wallet,
        ]);
    }
}
