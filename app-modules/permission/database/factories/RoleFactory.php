<?php

declare(strict_types=1);

namespace Termehsoft\Permission\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Permission\Models\Role;

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
