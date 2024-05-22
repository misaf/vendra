<?php

declare(strict_types=1);

namespace Database\Factories\User;

use App\Models\User\UserProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

final class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

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
            'birthdate'   => $this->faker->dateTimeBetween('-100 years', '-7 years'),
            'status'      => $this->faker->boolean(),
        ];
    }
}
