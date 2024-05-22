<?php

declare(strict_types=1);

namespace Database\Factories\Language;

use App\Models\Language\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

final class LanguageFactory extends Factory
{
    protected $model = Language::class;

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
            'iso_code'    => $this->faker->languageCode(),
            'status'      => $this->faker->boolean(),
        ];
    }
}
