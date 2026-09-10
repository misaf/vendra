<?php

declare(strict_types=1);

namespace Misaf\VendraTagger\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraTagger\Models\Tagger;

/**
 * @extends Factory<Tagger>
 */
final class TaggerFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => [app()->getLocale() => fake()->unique()->words(2, true)],
            'type' => fake()->optional()->word(),
        ];
    }
}
