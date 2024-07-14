<?php

declare(strict_types=1);

namespace Termehsoft\Language\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Language\Models\LanguageLine;

final class LanguageLineFactory extends Factory
{
    protected $model = LanguageLine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'group' => $this->faker->word(),
            'key'   => $this->faker->word(),
            'text'  => $this->faker->sentence(),
        ];
    }
}
