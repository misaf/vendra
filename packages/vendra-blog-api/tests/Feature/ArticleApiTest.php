<?php

declare(strict_types=1);

use Misaf\VendraBlog\Database\Factories\BlogPostCategoryFactory;
use Misaf\VendraBlog\Database\Factories\BlogPostFactory;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('exposes and filters active blog posts with storefront metadata', function (): void {
    $section = BlogPostCategoryFactory::new()->active()->create();
    $article = BlogPostFactory::new()->forCategory($section)->active()->create([
        'name' => ['en' => 'Rose care'],
        'slug' => ['en' => 'rose-care'],
    ]);
    $inactiveArticle = BlogPostFactory::new()->forCategory($section)->inactive()->create();
    $inactiveSection = BlogPostCategoryFactory::new()->inactive()->create();
    $hiddenArticle = BlogPostFactory::new()->forCategory($inactiveSection)->active()->create([
        'name' => ['en' => 'Hidden rose care'],
        'slug' => ['en' => 'hidden-rose-care'],
    ]);

    $this->getJson("/api/content/blog-posts?categoryId={$section->id}&search=rose", [
        'Accept' => 'application/vnd.api+json',
        'Accept-Language' => 'en',
    ])
        ->assertOk()
        ->assertJsonPath('meta.totalItems', 1)
        ->assertJsonPath('data.0.attributes.id', $article->id)
        ->assertJsonPath('data.0.attributes.slug.en', 'rose-care')
        ->assertJsonPath('data.0.attributes.blogPostCategory.id', $section->id);

    $this->getJson("/api/content/blog-post-categories/{$section->id}", ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('blogPosts.0.id', $article->id)
        ->assertJsonCount(1, 'blogPosts')
        ->assertJsonMissing(['id' => $inactiveArticle->id])
        ->assertJsonPath('active', true);

    $this->getJson("/api/content/blog-posts/{$hiddenArticle->id}", ['Accept' => 'application/ld+json'])
        ->assertNotFound();
});
