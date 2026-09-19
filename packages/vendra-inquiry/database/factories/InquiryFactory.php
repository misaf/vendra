<?php

declare(strict_types=1);

namespace Misaf\VendraInquiry\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraInquiry\Models\Inquiry;
use Misaf\VendraInquiry\States\Answered;
use Misaf\VendraInquiry\States\Closed;
use Misaf\VendraInquiry\States\Open;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<Inquiry>
 */
#[UseModel(Inquiry::class)]
final class InquiryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => null,
            'occasion' => fake()->randomElement(['wedding', 'event', 'sympathy', 'corporate', 'other']),
            'message' => fake()->paragraph(),
            'status' => Open::class,
            'source' => 'contact-form',
            'locale' => 'en',
        ];
    }

    public function answered(): static
    {
        return $this->state(fn (): array => [
            'status' => Answered::class,
            'answered_at' => now(),
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (): array => [
            'status' => Closed::class,
        ]);
    }

    public function forOccasion(string $occasion): static
    {
        return $this->state(fn (): array => ['occasion' => $occasion]);
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
}
