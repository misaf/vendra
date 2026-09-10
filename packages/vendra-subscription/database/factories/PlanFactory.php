<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Misaf\VendraSubscription\Enums\PeriodUnit;
use Misaf\VendraSubscription\Models\Plan;

/**
 * @extends Factory<Plan>
 */
#[UseModel(Plan::class)]
final class PlanFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'slug' => fn (array $attributes) => Str::slug($attributes['name']),
            'description' => fake()->text(),
            'max_units' => fake()->numberBetween(1, 10),
            'period_unit' => PeriodUnit::Month,
            'period_count' => 1,
            'grace_days' => 0,
            'price' => 0,
            'currency_code' => null,
            'trial_days' => 0,
            'features' => null,
            'active' => true,
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }

    public function trialDays(int $days): static
    {
        return $this->state(fn (): array => ['trial_days' => $days]);
    }

    /**
     * @param  list<string>  $features
     */
    public function withFeatures(array $features): static
    {
        return $this->state(fn (): array => ['features' => $features]);
    }

    public function graceDays(int $days): static
    {
        return $this->state(fn (): array => ['grace_days' => $days]);
    }

    public function priced(int $price, string $currencyCode = 'USD'): static
    {
        return $this->state(fn (): array => [
            'price' => $price,
            'currency_code' => $currencyCode,
        ]);
    }

    public function maxUnits(int $count): static
    {
        return $this->state(fn (): array => ['max_units' => $count]);
    }

    public function period(PeriodUnit $unit, int $count): static
    {
        return $this->state(fn (): array => [
            'period_unit' => $unit,
            'period_count' => $count,
        ]);
    }
}
