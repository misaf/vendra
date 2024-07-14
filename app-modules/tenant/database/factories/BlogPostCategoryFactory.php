<?php

declare(strict_types=1);

namespace Termehsoft\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Blog\Models\BlogPostCategory;

final class BlogPostCategoryFactory extends Factory
{
    protected $model = BlogPostCategory::class;

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
            'slug'        => $this->faker->slug(),
            'status'      => $this->faker->boolean(),
        ];
    }
}
