<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraSubscription\Enums\SubscriptionStatus;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;

/**
 * @extends Factory<Subscription>
 */
#[UseModel(Subscription::class)]
final class SubscriptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subscriber_type' => 'subscriber',
            'subscriber_id' => fake()->unique()->numberBetween(1, 2_000_000_000),
            'plan_id' => Plan::factory(),
            'status' => SubscriptionStatus::Active,
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addMonth(),
        ];
    }

    /**
     * Attach the subscription to a subscriber — a model (resolved
     * polymorphically) or a bare id under the default subscriber type.
     */
    public function forSubscriber(Model|int $subscriber): static
    {
        if ($subscriber instanceof Model) {
            return $this->state(fn (): array => [
                'subscriber_type' => $subscriber->getMorphClass(),
                'subscriber_id' => $subscriber->getKey(),
            ]);
        }

        return $this->state(fn (): array => ['subscriber_id' => $subscriber]);
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'status' => SubscriptionStatus::Expired,
            'starts_at' => now()->subMonths(2),
            'ends_at' => now()->subDay(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (): array => ['status' => SubscriptionStatus::Cancelled]);
    }

    public function neverExpires(): static
    {
        return $this->state(fn (): array => ['ends_at' => null]);
    }
}
