<?php

declare(strict_types=1);

use Misaf\VendraNewsletter\Actions\SubscribeNewsletterSubscriberAction;
use Misaf\VendraNewsletter\Database\Factories\NewsletterSubscriberFactory;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;
use Spatie\Multitenancy\Tasks\SwitchRouteCacheTask;

beforeEach(function (): void {
    config([
        'multitenancy.switch_tenant_tasks' => array_values(array_filter(
            config('multitenancy.switch_tenant_tasks'),
            fn (string $task): bool => $task !== SwitchRouteCacheTask::class,
        )),
    ]);

    makeCurrentTestTenant();
});

it('unsubscribes a subscriber when their unsubscribe link is visited', function (): void {
    $subscriber = NewsletterSubscriberFactory::new()->subscribed()->create();

    $response = $this->get(route('vendra-newsletter.unsubscribe', ['token' => $subscriber->unsubscribe_token]));

    $response->assertOk();

    expect($subscriber->refresh()->unsubscribed_at)->not->toBeNull()
        ->and($subscriber->isSubscribed())->toBeFalse();
});

it('keeps an already unsubscribed recipient unchanged', function (): void {
    $subscriber = NewsletterSubscriberFactory::new()->unsubscribed()->create();
    $unsubscribedAt = $subscriber->unsubscribed_at;

    $this->get(route('vendra-newsletter.unsubscribe', ['token' => $subscriber->unsubscribe_token]))
        ->assertOk();

    expect($subscriber->refresh()->unsubscribed_at->equalTo($unsubscribedAt))->toBeTrue();
});

it('shows the unknown page and changes nothing for an invalid token', function (): void {
    NewsletterSubscriberFactory::new()->subscribed()->count(2)->create();

    $this->get(route('vendra-newsletter.unsubscribe', ['token' => 'invalid-token']))
        ->assertOk();

    expect(NewsletterSubscriber::query()->unsubscribed()->count())->toBe(0);
});

it('restores and resubscribes a soft-deleted subscriber instead of creating a duplicate', function (): void {
    $subscriber = NewsletterSubscriberFactory::new()->unsubscribed()->create([
        'email' => 'restored@example.com',
        'name' => 'Original name',
    ]);
    $originalToken = $subscriber->unsubscribe_token;

    $subscriber->delete();

    $restoredSubscriber = resolve(SubscribeNewsletterSubscriberAction::class)->execute([
        'email' => 'restored@example.com',
        'name' => 'Restored name',
    ]);

    expect($restoredSubscriber->id)->toBe($subscriber->id)
        ->and($restoredSubscriber->trashed())->toBeFalse()
        ->and($restoredSubscriber->isSubscribed())->toBeTrue()
        ->and($restoredSubscriber->name)->toBe('Restored name')
        ->and($restoredSubscriber->unsubscribe_token)->toBe($originalToken)
        ->and(NewsletterSubscriber::query()->withTrashed()->where('email', 'restored@example.com')->count())->toBe(1);
});
