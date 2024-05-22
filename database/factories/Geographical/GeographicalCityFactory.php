<?php

declare(strict_types=1);

namespace Database\Factories\Geographical;

use App\Models\Geographical\GeographicalCity;
use App\Models\Geographical\GeographicalState;
use Illuminate\Database\Eloquent\Factories\Factory;

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
