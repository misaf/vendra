<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\Models\TransactionMetadata;

/** @extends Factory<TransactionMetadata> */
#[UseModel(TransactionMetadata::class)]
final class TransactionMetadataFactory extends Factory
{
    public function definition(): array
    {
        return [
            'transaction_id' => Transaction::factory(),
            'key_name' => fake()->unique()->word(),
            'key_value' => fake()->word(),
        ];
    }
}
