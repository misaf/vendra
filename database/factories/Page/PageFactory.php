<?php

declare(strict_types=1);

namespace Database\Factories\Page;

use App\Models\Page\Page;
use App\Models\Page\PageCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

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
