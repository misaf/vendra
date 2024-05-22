<?php

declare(strict_types=1);

namespace Database\Factories\Language;

use App\Models\Language\LanguageLine;
use Illuminate\Database\Eloquent\Factories\Factory;

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
