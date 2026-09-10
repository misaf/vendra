<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraBlog\Models\BlogPostCategory;
use Misaf\VendraSupport\Tenancy\TenantAwareness;

/**
 * @extends Factory<BlogPost>
 */
#[UseModel(BlogPost::class)]
final class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        return [
            'blog_post_category_id' => BlogPostCategory::factory(),
            'name' => ['en' => fake()->sentences(1, true)],
            'description' => ['en' => fake()->realTextBetween(100, 200)],
            'active' => fake()->boolean(80),
        ];
    }

    /**
     * No-op without a tenant provider, since there is no `tenant_id` column.
     */
    public function forTenant(Model|int $tenant): static
    {
        if (! TenantAwareness::enabled()) {
            return $this;
        }

        return $this->state(fn (): array => [
            'tenant_id' => $tenant instanceof Model ? $tenant->getKey() : $tenant,
        ]);
    }

    public function forCategory(BlogPostCategory $blogPostCategory): static
    {
        return $this->state(fn (): array => [
            'blog_post_category_id' => $blogPostCategory->id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (): array => ['active' => true]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['active' => false]);
    }
}
