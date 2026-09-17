<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SettingsProperty;
use App\Settings\SettingsScope;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SettingsProperty>
 */
#[UseModel(SettingsProperty::class)]
final class SettingsPropertyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'scope' => SettingsScope::forTenant(null),
            'group' => fake()->unique()->word(),
            'name' => fake()->unique()->word(),
            'locked' => false,
            'payload' => json_encode(fake()->word()),
        ];
    }
}
