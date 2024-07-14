<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Geographical\Models\GeographicalCity;
use Termehsoft\Geographical\Models\GeographicalState;

final class GeographicalCityFactory extends Factory
{
    protected $model = GeographicalCity::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'geographical_state_id' => GeographicalState::factory(),
            'name'                  => $this->faker->sentence(),
            'description'           => $this->faker->paragraph(),
            'slug'                  => $this->faker->slug(),
            'status'                => $this->faker->boolean(),
        ];
    }
}
