<?php

declare(strict_types=1);

namespace Misaf\VendraSubscription\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Misaf\VendraSubscription\Enums\PeriodUnit;
use Misaf\VendraSubscription\Models\Plan;

final class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->plans() as $plan) {
            Plan::query()->firstOrCreate(['slug' => Arr::get($plan, 'slug')], $plan);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function plans(): array
    {
        return [
            [
                'name' => 'Free',
                'slug' => 'free',
                'max_units' => 1,
                'period_unit' => PeriodUnit::Month,
                'period_count' => 1,
                'grace_days' => 0,
                'price' => 0,
                'currency_code' => null,
                'trial_days' => 0,
                'features' => [],
                'active' => true,
            ],
            [
                'name' => 'Basic',
                'slug' => 'basic',
                'max_units' => 3,
                'period_unit' => PeriodUnit::Month,
                'period_count' => 1,
                'grace_days' => 7,
                'price' => 1900,
                'currency_code' => 'USD',
                'trial_days' => 14,
                'features' => ['custom_domain'],
                'active' => true,
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'max_units' => 10,
                'period_unit' => PeriodUnit::Month,
                'period_count' => 1,
                'grace_days' => 14,
                'price' => 4900,
                'currency_code' => 'USD',
                'trial_days' => 14,
                'features' => ['custom_domain', 'priority_support'],
                'active' => true,
            ],
        ];
    }
}
