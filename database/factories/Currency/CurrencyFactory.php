<?php

declare(strict_types=1);

namespace Database\Factories\Currency;

use App\Models\Currency\Currency;
use App\Models\Currency\CurrencyCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

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
