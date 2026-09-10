<?php

declare(strict_types=1);

namespace Misaf\VendraTransaction\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Misaf\VendraSupport\Tenancy\TenantAwareness;
use Misaf\VendraTransaction\Models\Wallet;
use Misaf\VendraTransaction\Support\TransactionUsers;

/** @extends Factory<Wallet> */
#[UseModel(Wallet::class)]
final class WalletFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => TransactionUsers::model()::factory(),
            'currency_code' => 'USD',
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

    public function forUser(Model|int $user): static
    {
        return $this->state(fn (): array => [
            'user_id' => $user instanceof Model ? $user->getKey() : $user,
        ]);
    }

    public function forCurrency(string $currencyCode): static
    {
        return $this->state(fn (): array => [
            'currency_code' => Str::upper($currencyCode),
        ]);
    }
}
