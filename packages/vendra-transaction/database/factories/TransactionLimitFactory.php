<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\TransactionLimit;
use Misaf\VendraTransaction\Models\Wallet;

/** @extends Factory<TransactionLimit> */
#[UseModel(TransactionLimit::class)]
final class TransactionLimitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'transaction_type' => fake()->randomElement(TransactionTypeEnum::cases()),
            'amount' => fake()->numberBetween(10_000, 10_000_000),
        ];
    }

    public function forWallet(Wallet|int $wallet): static
    {
        return $this->state(fn (): array => [
            'wallet_id' => $wallet instanceof Wallet ? $wallet->id : $wallet,
        ]);
    }

    public function ofType(TransactionTypeEnum $transactionType): static
    {
        return $this->state(fn (): array => ['transaction_type' => $transactionType]);
    }
}
