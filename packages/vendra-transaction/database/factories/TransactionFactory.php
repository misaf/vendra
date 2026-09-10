<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraTransaction\Enums\TransactionTypeEnum;
use Misaf\VendraTransaction\Models\Transaction;
use Misaf\VendraTransaction\Models\TransactionGateway;
use Misaf\VendraTransaction\Models\Wallet;
use Misaf\VendraTransaction\States\Approved;
use Misaf\VendraTransaction\States\Declined;
use Misaf\VendraTransaction\States\Failed;
use Misaf\VendraTransaction\States\Pending;
use Misaf\VendraTransaction\States\Processing;
use Misaf\VendraTransaction\States\Review;

/** @extends Factory<Transaction> */
#[UseModel(Transaction::class)]
final class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'wallet_id' => Wallet::factory(),
            'transaction_gateway_id' => TransactionGateway::factory(),
            'transaction_type' => fake()->randomElement(TransactionTypeEnum::cases()),
            'amount' => fake()->numberBetween(1_000, 1_000_000),
            'status' => Pending::class,
        ];
    }

    public function forTenant(Model|int $tenant): static
    {
        if (! TenantAwareness::enabled()) {
            return $this;
        }

        return $this->state(fn (): array => [
            'tenant_id' => $tenant instanceof Model ? $tenant->getKey() : $tenant,
        ]);
    }

    public function forWallet(Wallet|int $wallet): static
    {
        return $this->state(fn (): array => [
            'wallet_id' => $wallet instanceof Wallet ? $wallet->id : $wallet,
        ]);
    }

    public function forUser(Model|int $user): static
    {
        $userId = $user instanceof Model ? $user->getKey() : $user;

        return $this->state(fn (): array => [
            'wallet_id' => Wallet::query()->firstOrCreate([
                'user_id' => $userId,
                'currency_code' => 'USD',
            ])->id,
        ]);
    }

    public function forGateway(TransactionGateway|int $transactionGateway): static
    {
        return $this->state(fn (): array => [
            'transaction_gateway_id' => $transactionGateway instanceof TransactionGateway ? $transactionGateway->id : $transactionGateway,
        ]);
    }

    public function ofType(TransactionTypeEnum $transactionType): static
    {
        return $this->state(fn (): array => ['transaction_type' => $transactionType]);
    }

    public function deposit(): static
    {
        return $this->ofType(TransactionTypeEnum::Deposit);
    }

    public function withdrawal(): static
    {
        return $this->ofType(TransactionTypeEnum::Withdrawal);
    }

    public function commission(): static
    {
        return $this->ofType(TransactionTypeEnum::Commission);
    }

    public function bonus(): static
    {
        return $this->ofType(TransactionTypeEnum::Bonus);
    }

    public function transfer(): static
    {
        return $this->ofType(TransactionTypeEnum::Transfer);
    }

    public function pending(): static
    {
        return $this->state(fn (): array => ['status' => Pending::class]);
    }

    public function processing(): static
    {
        return $this->state(fn (): array => ['status' => Processing::class]);
    }

    public function review(): static
    {
        return $this->state(fn (): array => ['status' => Review::class]);
    }

    /**
     * Sets the stored status directly, without settling into the ledger.
     * Use `->create()->approve()` instead when the test needs ledger
     * entries and balances to exist.
     */
    public function approved(): static
    {
        return $this->state(fn (): array => ['status' => Approved::class]);
    }

    public function declined(): static
    {
        return $this->state(fn (): array => ['status' => Declined::class]);
    }

    public function failed(): static
    {
        return $this->state(fn (): array => ['status' => Failed::class]);
    }
}
