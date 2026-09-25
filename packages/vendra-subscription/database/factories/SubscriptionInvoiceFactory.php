<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Models\SubscriptionInvoice;
use Misaf\VendraSubscription\Models\SubscriptionPayment;

/**
 * @extends Factory<SubscriptionInvoice>
 */
#[UseModel(SubscriptionInvoice::class)]
final class SubscriptionInvoiceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscription_payment_id' => SubscriptionPayment::factory(),
            'subscriber_type' => 'subscriber',
            'subscriber_id' => fake()->unique()->numberBetween(1, 2_000_000_000),
            'number' => 'INV-'.now()->year.'-'.str_pad((string) fake()->unique()->numberBetween(1, 999_999), 6, '0', STR_PAD_LEFT),
            'issued_at' => now(),
            'currency_code' => 'USD',
            'net_amount' => 1_000,
            'tax_rate' => 1_900,
            'tax_label' => 'VAT',
            'tax_amount' => 190,
            'total_amount' => 1_190,
            'seller' => ['name' => 'Vendra', 'address' => null, 'tax_id' => null],
            'buyer' => ['name' => fake()->company(), 'email' => fake()->safeEmail(), 'address' => null, 'tax_id' => null],
            'lines' => [['description' => 'Starter', 'period_start' => null, 'period_end' => null, 'prorated' => false, 'amount' => 1_000]],
        ];
    }

    public function forSubscriber(Model $subscriber): static
    {
        return $this->state(fn (): array => [
            'subscriber_type' => $subscriber->getMorphClass(),
            'subscriber_id' => $subscriber->getKey(),
        ]);
    }
}
