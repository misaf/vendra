<?php

declare(strict_types=1);

namespace Termehsoft\Faq\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Faq\Models\Faq;
use Termehsoft\Faq\Models\FaqCategory;

final class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'faq_category_id' => FaqCategory::factory(),
            'name'            => $this->faker->sentence(),
            'description'     => $this->faker->paragraph(),
            'slug'            => $this->faker->slug(),
            'status'          => $this->faker->boolean(),
        ];
    }
}
