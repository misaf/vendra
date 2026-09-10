<?php

declare(strict_types=1);

namespace Misaf\VendraLanguage\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Misaf\VendraLanguage\Models\Language;
use Misaf\VendraLanguage\Support\Locales;

/**
 * @extends Factory<Language>
 */
#[UseModel(Language::class)]
final class LanguageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'locale' => fake()->unique()->randomElement(Locales::configured()),
            'active' => true,
            'is_default' => false,
            'position' => fake()->numberBetween(1, 1000),
        ];
    }

    public function default(): static
    {
        return $this->state(fn (): array => ['is_default' => true]);
    }
}
