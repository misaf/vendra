<?php

declare(strict_types=1);

namespace Termehsoft\Geographical\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Geographical\Models\GeographicalZone;

final class GeographicalZoneFactory extends Factory
{
    protected $model = GeographicalZone::class;

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
