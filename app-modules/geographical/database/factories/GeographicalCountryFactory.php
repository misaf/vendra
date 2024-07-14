<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Geographical\Models\GeographicalCountry;
use Termehsoft\Geographical\Models\GeographicalZone;

final class GeographicalCountryFactory extends Factory
{
    protected $model = GeographicalCountry::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'geographical_zone_id' => GeographicalZone::factory(),
            'name'                 => $this->faker->sentence(),
            'description'          => $this->faker->paragraph(),
            'slug'                 => $this->faker->slug(),
            'status'               => $this->faker->boolean(),
        ];
    }
}
