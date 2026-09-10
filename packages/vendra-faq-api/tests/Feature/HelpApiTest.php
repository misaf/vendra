<?php

declare(strict_types=1);

use Misaf\VendraFaq\Database\Factories\FaqCategoryFactory;
use Misaf\VendraFaq\Database\Factories\FaqFactory;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('exposes and filters active FAQs with storefront metadata', function (): void {
    $topic = FaqCategoryFactory::new()->active()->create();
    $article = FaqFactory::new()->forCategory($topic)->active()->create([
        'name' => ['en' => 'How are roses delivered?'],
        'slug' => ['en' => 'rose-delivery'],
    ]);
    $inactiveArticle = FaqFactory::new()->forCategory($topic)->inactive()->create();
    $inactiveTopic = FaqCategoryFactory::new()->inactive()->create();
    $hiddenArticle = FaqFactory::new()->forCategory($inactiveTopic)->active()->create([
        'name' => ['en' => 'How are hidden roses delivered?'],
        'slug' => ['en' => 'hidden-rose-delivery'],
    ]);

    $this->getJson("/api/content/faqs?categoryId={$topic->id}&search=roses&sort=position", [
        'Accept' => 'application/vnd.api+json',
        'Accept-Language' => 'en',
    ])
        ->assertOk()
        ->assertJsonPath('meta.totalItems', 1)
        ->assertJsonPath('data.0.attributes.id', $article->id)
        ->assertJsonPath('data.0.attributes.slug.en', 'rose-delivery')
        ->assertJsonPath('data.0.attributes.faqCategory.id', $topic->id);

    $this->getJson("/api/content/faq-categories/{$topic->id}", ['Accept' => 'application/ld+json'])
        ->assertOk()
        ->assertJsonPath('faqs.0.id', $article->id)
        ->assertJsonCount(1, 'faqs')
        ->assertJsonMissing(['id' => $inactiveArticle->id])
        ->assertJsonPath('active', true);

    $this->getJson("/api/content/faqs/{$hiddenArticle->id}", ['Accept' => 'application/ld+json'])
        ->assertNotFound();
});
