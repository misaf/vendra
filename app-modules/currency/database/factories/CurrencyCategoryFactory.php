<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Currency\Models\CurrencyCategory;

final class CurrencyCategoryFactory extends Factory
{
    protected $model = CurrencyCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'slug'        => $this->faker->slug(),
            'status'      => $this->faker->boolean(),
        ];
    }
}
