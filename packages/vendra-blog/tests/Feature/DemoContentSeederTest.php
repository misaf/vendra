<?php

declare(strict_types=1);

use Misaf\VendraBlog\Database\Seeders\DemoContentSeeder;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraBlog\Models\BlogPostCategory;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    $blogPostCategories = BlogPostCategory::query()->count();
    $blogPosts = BlogPost::query()->count();

    expect($blogPostCategories)->toBeGreaterThan(0)
        ->and($blogPosts)->toBeGreaterThan(0);

    resolve(DemoContentSeeder::class)->run();

    expect(BlogPostCategory::query()->count())->toBe($blogPostCategories)
        ->and(BlogPost::query()->count())->toBe($blogPosts);
});
