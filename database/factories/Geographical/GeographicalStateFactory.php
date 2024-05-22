<?php

declare(strict_types=1);

namespace Database\Factories\Geographical;

use App\Models\Geographical\GeographicalCountry;
use App\Models\Geographical\GeographicalState;
use Illuminate\Database\Eloquent\Factories\Factory;

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
