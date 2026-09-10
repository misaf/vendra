<?php

declare(strict_types=1);

use Misaf\VendraNewsletter\Actions\SubscribeNewsletterSubscriberAction;
use Misaf\VendraNewsletter\Database\Factories\NewsletterSubscriberFactory;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('creates a new subscribed subscriber for an unknown email', function (): void {
    $subscriber = resolve(SubscribeNewsletterSubscriberAction::class)->execute([
        'email' => 'new@example.com',
        'name' => 'New subscriber',
    ]);

    expect($subscriber->wasRecentlyCreated)->toBeTrue()
        ->and($subscriber->isSubscribed())->toBeTrue()
        ->and(NewsletterSubscriber::query()->where('email', 'new@example.com')->count())->toBe(1);
});

it('returns the existing active subscriber unchanged for a repeat subscription', function (): void {
    $existing = NewsletterSubscriberFactory::new()->create([
        'email' => 'repeat@example.com',
        'name' => 'Original name',
    ]);

    $subscriber = resolve(SubscribeNewsletterSubscriberAction::class)->execute([
        'email' => 'repeat@example.com',
        'name' => 'Other name',
    ]);

    expect($subscriber->id)->toBe($existing->id)
        ->and($subscriber->name)->toBe('Original name')
        ->and(NewsletterSubscriber::query()->withTrashed()->where('email', 'repeat@example.com')->count())->toBe(1);
});
