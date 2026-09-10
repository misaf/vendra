<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<NewsletterSubscriber>
 */
#[UseModel(NewsletterSubscriber::class)]
final class NewsletterSubscriberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->name(),
        ];
    }

    /**
     * No-op without a tenant provider, since there is no `tenant_id` column.
     */
    public function forTenant(Model|int $tenant): static
    {
        if (! TenantAwareness::enabled()) {
            return $this;
        }

        return $this->state(fn (): array => [
            'tenant_id' => $tenant instanceof Model ? $tenant->getKey() : $tenant,
        ]);
    }

    public function subscribed(): static
    {
        return $this->state(fn (): array => [
            'subscribed_at' => now()->subMonth(),
            'unsubscribed_at' => null,
        ]);
    }

    public function unsubscribed(): static
    {
        return $this->state(fn (): array => [
            'subscribed_at' => now()->subMonths(2),
            'unsubscribed_at' => now()->subWeek(),
        ]);
    }
}
