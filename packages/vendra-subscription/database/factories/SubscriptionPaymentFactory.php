<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Misaf\VendraSubscription\Enums\SubscriptionPaymentStatus;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

/**
 * @extends Factory<SubscriptionPayment>
 */
#[UseModel(SubscriptionPayment::class)]
final class SubscriptionPaymentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscription_id' => Subscription::factory(),
            'payer_type' => 'payer',
            'payer_id' => fake()->unique()->numberBetween(1, 2_000_000_000),
            'provider' => 'testing',
            'idempotency_key' => (string) Str::uuid(),
            'amount' => 1_500,
            'currency_code' => 'USD',
            'status' => SubscriptionPaymentStatus::Pending,
        ];
    }

    public function forPayer(Model $payer): static
    {
        return $this->state(fn (): array => [
            'payer_type' => $payer->getMorphClass(),
            'payer_id' => $payer->getKey(),
        ]);
    }
}
