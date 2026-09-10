<?php

declare(strict_types=1);

namespace Misaf\VendraBlog\Database\Seeders;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Misaf\VendraBlog\Database\Factories\BlogPostCategoryFactory;
use Misaf\VendraBlog\Database\Factories\BlogPostFactory;
use Misaf\VendraBlog\Models\BlogPostCategory;
use Misaf\VendraSupport\Tenancy\Database\Seeders\DemoContentSeeder as BaseDemoContentSeeder;
use Misaf\VendraSupport\Tenancy\RequiresCurrentTenant;

final class DemoContentSeeder extends BaseDemoContentSeeder
{
    use RequiresCurrentTenant;

    protected function seedFactories(): void
    {
        $this->currentTenantOrNull();

        BlogPostCategoryFactory::new()
            ->active()
            ->count(4)
            ->create()
            ->each(fn (BlogPostCategory $blogPostCategory): mixed => BlogPostFactory::new()
                ->forCategory($blogPostCategory)
                ->active()
                ->count(3)
                ->create());
    }

    /**
     * @param  list<array<string, mixed>>  $records
     */
    protected function seedFixtures(array $records): void
    {
        $this->currentTenantOrNull();

        foreach ($records as $record) {
            $this->seedFixtureRecord($record);
        }
    }

    /**
     * @param  array<string, mixed>  $record
     */
    protected function seedFixtureRecord(array $record): void
    {
        $this->handleSeedFixtureRecord($this->validatedFixtureRecord($record));
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool,
     *     blog_posts: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         active: bool
     *     }>
     * } $data
     */
    private function handleSeedFixtureRecord(array $data): void
    {
        $blogPostCategory = BlogPostCategory::query()->create([
            'name' => Arr::get($data, 'name'),
            'description' => Arr::get($data, 'description'),
            'slug' => Arr::get($data, 'slug'),
            'active' => Arr::get($data, 'active'),
        ]);

        foreach (Arr::get($data, 'blog_posts') as $blogPostRecord) {
            $this->handleBlogPostFixtureRecord($blogPostCategory, $blogPostRecord);
        }
    }

    /**
     * @param array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool
     * } $blogPostRecord
     */
    private function handleBlogPostFixtureRecord(BlogPostCategory $blogPostCategory, array $blogPostRecord): void
    {
        $blogPostCategory->blogPosts()->create([
            'name' => Arr::get($blogPostRecord, 'name'),
            'description' => Arr::get($blogPostRecord, 'description'),
            'slug' => Arr::get($blogPostRecord, 'slug'),
            'active' => Arr::get($blogPostRecord, 'active'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array{
     *     name: non-empty-array<string, string>,
     *     description: non-empty-array<string, string>,
     *     slug: non-empty-array<string, string>,
     *     active: bool,
     *     blog_posts: list<array{
     *         name: non-empty-array<string, string>,
     *         description: non-empty-array<string, string>,
     *         slug: non-empty-array<string, string>,
     *         active: bool
     *     }>
     * }
     */
    private function validatedFixtureRecord(array $record): array
    {
        /** @var array{
         *     name: non-empty-array<string, string>,
         *     description: non-empty-array<string, string>,
         *     slug: non-empty-array<string, string>,
         *     active: bool,
         *     blog_posts: list<array{
         *         name: non-empty-array<string, string>,
         *         description: non-empty-array<string, string>,
         *         slug: non-empty-array<string, string>,
         *         active: bool
         *     }>
         * } $validated
         */
        $validated = Validator::make(
            data: $record,
            rules: [
                'name' => ['required', 'array', 'min:1'],
                'name.*' => ['required', 'string'],
                'description' => ['required', 'array', 'min:1'],
                'description.*' => ['required', 'string'],
                'slug' => ['required', 'array', 'min:1'],
                'slug.*' => ['required', 'string'],
                'active' => ['required', 'boolean'],
                'blog_posts' => ['required', 'array', 'list'],
                'blog_posts.*' => ['required', 'array:name,description,slug,active'],
                'blog_posts.*.name' => ['required', 'array', 'min:1'],
                'blog_posts.*.name.*' => ['required', 'string'],
                'blog_posts.*.description' => ['required', 'array', 'min:1'],
                'blog_posts.*.description.*' => ['required', 'string'],
                'blog_posts.*.slug' => ['required', 'array', 'min:1'],
                'blog_posts.*.slug.*' => ['required', 'string'],
                'blog_posts.*.active' => ['required', 'boolean'],
            ],
        )->validate();

        return $validated;
    }
}
