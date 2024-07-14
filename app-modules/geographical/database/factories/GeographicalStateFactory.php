<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Geographical\Models\GeographicalCountry;
use Termehsoft\Geographical\Models\GeographicalState;

final class GeographicalStateFactory extends Factory
{
    protected $model = GeographicalState::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'geographical_country_id' => GeographicalCountry::factory(),
            'name'                    => $this->faker->sentence(),
            'description'             => $this->faker->paragraph(),
            'slug'                    => $this->faker->slug(),
            'status'                  => $this->faker->boolean(),
        ];
    }
}
