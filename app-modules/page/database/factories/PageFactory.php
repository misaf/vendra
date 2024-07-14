<?php

declare(strict_types=1);

namespace Termehsoft\Page\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Termehsoft\Page\Models\Page;
use Termehsoft\Page\Models\PageCategory;

final class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_category_id' => PageCategory::factory(),
            'name'             => $this->faker->name(),
            'description'      => $this->faker->text(),
            'slug'             => $this->faker->slug(),
            'status'           => $this->faker->boolean(),
        ];
    }
}
