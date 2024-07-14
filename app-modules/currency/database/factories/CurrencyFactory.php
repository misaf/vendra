<?php

declare(strict_types=1);

namespace Termehsoft\Currency\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Currency\Models\Currency;
use Termehsoft\Currency\Models\CurrencyCategory;

final class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency_category_id' => CurrencyCategory::factory(),
            'name'                 => $this->faker->sentence(),
            'description'          => $this->faker->paragraph(),
            'slug'                 => $this->faker->slug(),
            'iso_code'             => $this->faker->languageCode(),
            'is_default'           => $this->faker->boolean(),
            'status'               => $this->faker->boolean(),
        ];
    }
}
