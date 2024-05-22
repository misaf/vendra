<?php

declare(strict_types=1);

namespace Database\Factories\Permission;

use App\Models\Permission\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

final class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'       => $this->faker->sentence(),
            'guard_name' => null,
        ];
    }
}
