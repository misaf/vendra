<?php

declare(strict_types=1);

namespace Misaf\VendraStore\Database\Factories;

use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;

/**
 * @extends Factory<StoreDomain>
 */
#[UseModel(StoreDomain::class)]
final class StoreDomainFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'name' => fake()->unique()->sentence(3),
            'description' => fake()->text(),
            'slug' => fn (array $attributes) => Str::slug(Arr::get($attributes, 'name')),
            'active' => fake()->boolean(),
        ];
    }

    public function forStore(Store $store): static
    {
        return $this->state(fn (): array => [
            'store_id' => $store->id,
        ]);
    }
}
