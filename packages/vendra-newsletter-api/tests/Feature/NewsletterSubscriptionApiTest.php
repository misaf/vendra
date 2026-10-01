<?php

declare(strict_types=1);

use Misaf\VendraNewsletter\Database\Factories\NewsletterSubscriberFactory;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

it('accepts a guest subscription without exposing subscriber data or client-controlled state', function (): void {
    $this->postJson('/api/marketing/newsletter-subscriptions', [
        'email' => ' Reader@Example.com ',
        'name' => ' Reader ',
        'tenant_id' => 999,
        'unsubscribe_token' => 'client-token',
        'unsubscribed_at' => '2026-01-01',
    ])->assertNoContent();

    $subscriber = NewsletterSubscriber::query()->sole();
    expect($subscriber)->email->toBe('reader@example.com')->name->toBe('Reader')
        ->isSubscribed()->toBeTrue()->unsubscribe_token->not->toBe('client-token');
});

it('keeps repeat subscriptions idempotent and preserves the existing subscriber name and token', function (): void {
    $subscriber = NewsletterSubscriberFactory::new()->subscribed()->createOne([
        'email' => 'repeat@example.com', 'name' => 'Original',
    ]);
    $subscribedAt = $subscriber->subscribed_at;
    $token = $subscriber->unsubscribe_token;

    $this->postJson('/api/marketing/newsletter-subscriptions', [
        'email' => 'REPEAT@example.com', 'name' => 'Changed',
    ])->assertNoContent();
    $this->postJson('/api/marketing/newsletter-subscriptions', ['email' => 'repeat@example.com'])->assertNoContent();

    expect(NewsletterSubscriber::query()->count())->toBe(1)
        ->and($subscriber->fresh())->name->toBe('Original')->unsubscribe_token->toBe($token)
        ->and($subscriber->fresh()?->subscribed_at?->equalTo($subscribedAt))->toBeTrue();
});

it('resubscribes an opted-out subscriber while preserving its unsubscribe token', function (): void {
    $subscriber = NewsletterSubscriberFactory::new()->unsubscribed()->createOne(['email' => 'optout@example.com']);
    $token = $subscriber->unsubscribe_token;

    $this->postJson('/api/marketing/newsletter-subscriptions', ['email' => 'optout@example.com'])->assertNoContent();

    expect($subscriber->fresh())->isSubscribed()->toBeTrue()->unsubscribe_token->toBe($token);
});

it('restores a deleted subscriber instead of creating a duplicate', function (): void {
    $subscriber = NewsletterSubscriberFactory::new()->createOne(['email' => 'deleted@example.com']);
    $token = $subscriber->unsubscribe_token;
    $subscriber->delete();

    $this->postJson('/api/marketing/newsletter-subscriptions', [
        'email' => 'deleted@example.com', 'name' => 'Restored',
    ])->assertNoContent();

    expect(NewsletterSubscriber::query()->withTrashed()->count())->toBe(1)
        ->and($subscriber->fresh())->trashed()->toBeFalse()->name->toBe('Restored')
        ->isSubscribed()->toBeTrue()->unsubscribe_token->toBe($token);
});

it('rejects invalid subscriptions without writing a subscriber', function (array $payload, string $field): void {
    $this->postJson('/api/marketing/newsletter-subscriptions', $payload)
        ->assertUnprocessable()->assertJsonPath('violations.0.propertyPath', $field);

    expect(NewsletterSubscriber::query()->count())->toBe(0);
})->with([
    'email required' => [[], 'email'],
    'email null' => [['email' => null], 'email'],
    'email wrong type' => [['email' => ['invalid']], 'email'],
    'name wrong type' => [['email' => 'reader@example.com', 'name' => ['invalid']], 'name'],
    'email invalid' => [['email' => 'invalid'], 'email'],
    'email too long' => [['email' => str_repeat('a', 250).'@example.com'], 'email'],
    'name too long' => [['email' => 'reader@example.com', 'name' => str_repeat('a', 256)], 'name'],
]);

it('subscribes the same email independently in different tenants', function (): void {
    $firstTenant = currentTestTenant();
    $subscriber = NewsletterSubscriberFactory::new()->unsubscribed()->createOne(['email' => 'shared@example.com']);
    switchToTestTenant(createTestTenant());

    $this->postJson('/api/marketing/newsletter-subscriptions', ['email' => 'shared@example.com'])->assertNoContent();

    expect(NewsletterSubscriber::query()->sole())->isSubscribed()->toBeTrue()->id->not->toBe($subscriber->id);
    switchToTestTenant($firstTenant);
    expect($subscriber->fresh()?->isSubscribed())->toBeFalse();
});

it('throttles repeated public subscription requests', function (): void {
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $this->postJson('/api/marketing/newsletter-subscriptions', ['email' => 'limited@example.com'])->assertNoContent();
    }

    $this->postJson('/api/marketing/newsletter-subscriptions', ['email' => 'blocked@example.com'])->assertTooManyRequests();

    expect(NewsletterSubscriber::query()->count())->toBe(1);
});

it('does not expose subscriber read or deletion operations', function (): void {
    $subscriber = NewsletterSubscriberFactory::new()->createOne();

    $this->getJson('/api/marketing/newsletter-subscriptions')->assertMethodNotAllowed();
    $this->getJson('/api/marketing/newsletter-subscriptions/'.$subscriber->id)->assertNotFound();
    $this->deleteJson('/api/marketing/newsletter-subscriptions/'.$subscriber->id)->assertNotFound();

    $this->assertModelExists($subscriber);
});

it('refuses subscriptions when the request has no resolved tenant', function (): void {
    forgetCurrentTestTenant();

    $this->postJson('/api/marketing/newsletter-subscriptions', ['email' => 'unscoped@example.com'])->assertNotFound();

    expect(NewsletterSubscriber::query()->count())->toBe(0);
});
