<?php

declare(strict_types=1);

namespace Misaf\VendraNewsletter\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<Newsletter>
 */
#[UseModel(Newsletter::class)]
final class NewsletterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(6),
            'content' => fake()->realTextBetween(200, 400),
            'status' => NewsletterStatusEnum::Draft,
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

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => NewsletterStatusEnum::Draft,
            'scheduled_at' => null,
            'sent_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (): array => [
            'status' => NewsletterStatusEnum::Scheduled,
            'scheduled_at' => now()->addDay(),
            'sent_at' => null,
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (): array => [
            'status' => NewsletterStatusEnum::Sent,
            'scheduled_at' => null,
            'sent_at' => now()->subDay(),
        ]);
    }
}
