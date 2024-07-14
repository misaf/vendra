<?php

declare(strict_types=1);

namespace Termehsoft\Transaction\Database\Factories;

use App\Enums\TransactionStatusEnum;
use App\Services\TransactionService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Transaction\Models\Transaction;

final class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference_code' => TransactionService::generateReferenceCode(),
            'amount'         => $this->faker->numberBetween(100, 200),
            'status'         => $this->faker->randomElement(TransactionStatusEnum::cases()),
        ];
    }
}
