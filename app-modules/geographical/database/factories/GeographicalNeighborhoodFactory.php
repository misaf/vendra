<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Geographical\Models\GeographicalCity;
use Termehsoft\Geographical\Models\GeographicalNeighborhood;

final class GeographicalNeighborhoodFactory extends Factory
{
    protected $model = GeographicalNeighborhood::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'geographical_city_id' => GeographicalCity::factory(),
            'name'                 => $this->faker->sentence(),
            'description'          => $this->faker->paragraph(),
            'slug'                 => $this->faker->slug(),
            'status'               => $this->faker->boolean(),
        ];
    }
}
