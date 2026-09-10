<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Gate;
use Misaf\VendraNewsletter\Enums\NewsletterPolicyEnum;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Enums\NewsletterSubscriberPolicyEnum;
use Misaf\VendraNewsletter\Filament\Clusters\Resources\Newsletters\Actions\SendNewsletterTableAction;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraNewsletter\Models\NewsletterSubscriber;
use Misaf\VendraNewsletter\Policies\NewsletterPolicy;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;
use Misaf\VendraSupport\Tenancy\Scopes\TeamScope;
use Misaf\VendraSupport\Tenancy\Scopes\TenantScope;
use Misaf\VendraSupport\Tenancy\TenantSchema;

/**
 * Bind a tenant resolver reporting the given availability for the current test.
 */
function fakeNewsletterTenantResolver(bool $available, ?int $currentId = null): TenantResolver
{
    $resolver = Mockery::mock(TenantResolver::class);
    $resolver->shouldReceive('available')->andReturn($available);
    $resolver->shouldReceive('currentId')->andReturn($currentId);
    $resolver->shouldReceive('foreignKey')->andReturn(TenantSchema::DEFAULT_FOREIGN_KEY);

    return $resolver;
}

it('defines the expected fillable and hidden attributes', function (): void {
    expect((new Newsletter)->getFillable())->toContain('subject', 'content', 'status', 'scheduled_at', 'sent_at')
        ->and((new Newsletter)->getHidden())->toContain('tenant_id')
        ->and((new NewsletterSubscriber)->getFillable())->toContain('email', 'name', 'subscribed_at', 'unsubscribed_at')
        ->and((new NewsletterSubscriber)->getHidden())->toContain('tenant_id', 'unsubscribe_token');
});

it('casts the newsletter status to its enum', function (): void {
    $newsletter = new Newsletter;
    $newsletter->status = NewsletterStatusEnum::Scheduled;

    expect($newsletter->status)->toBe(NewsletterStatusEnum::Scheduled);
});

it('applies shared tenant ownership to newsletter models', function (): void {
    expect(class_uses_recursive(Newsletter::class))->toContain(BelongsToTenant::class)
        ->and(class_uses_recursive(NewsletterSubscriber::class))->toContain(BelongsToTenant::class);
});

it('always registers tenant scopes that self-disable without a current tenant', function (): void {
    Newsletter::clearBootedModels();
    app()->instance(TenantResolver::class, fakeNewsletterTenantResolver(available: false));

    expect(array_keys((new Newsletter)->getGlobalScopes()))->toContain(TenantScope::class, TeamScope::class);

    Newsletter::clearBootedModels();
});

it('reports a subscriber as subscribed only while it has no unsubscribed timestamp', function (): void {
    $subscriber = new NewsletterSubscriber;

    expect($subscriber->isSubscribed())->toBeTrue();

    $subscriber->unsubscribed_at = now();

    expect($subscriber->isSubscribed())->toBeFalse();
});

it('defines the newsletter status enum cases', function (): void {
    expect(array_column(NewsletterStatusEnum::cases(), 'value'))->toBe([
        'draft',
        'scheduled',
        'sent',
    ]);
});

it('defines policy permissions for all newsletter resources', function (): void {
    expect(array_column(NewsletterPolicyEnum::cases(), 'value'))->toBe([
        'create-newsletter',
        'delete-newsletter',
        'delete-any-newsletter',
        'force-delete-newsletter',
        'force-delete-any-newsletter',
        'restore-newsletter',
        'restore-any-newsletter',
        'send-newsletter',
        'update-newsletter',
        'view-newsletter',
        'view-any-newsletter',
    ])
        ->and(array_column(NewsletterSubscriberPolicyEnum::cases(), 'value'))->toBe([
            'create-newsletter-subscriber',
            'delete-newsletter-subscriber',
            'delete-any-newsletter-subscriber',
            'force-delete-newsletter-subscriber',
            'force-delete-any-newsletter-subscriber',
            'restore-newsletter-subscriber',
            'restore-any-newsletter-subscriber',
            'update-newsletter-subscriber',
            'view-newsletter-subscriber',
            'view-any-newsletter-subscriber',
        ]);
});

it('prevents sent newsletters from being updated', function (): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')
        ->once()
        ->with(NewsletterPolicyEnum::Update->value)
        ->andReturnTrue();

    $draft = new Newsletter;
    $draft->status = NewsletterStatusEnum::Draft;

    $sent = new Newsletter;
    $sent->status = NewsletterStatusEnum::Sent;

    $policy = new NewsletterPolicy;

    expect($policy->update($user, $draft))->toBeTrue()
        ->and($policy->update($user, $sent))->toBeFalse();
});

it('authorizes newsletter sending through its dedicated permission', function (): void {
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')
        ->once()
        ->with(NewsletterPolicyEnum::Send->value)
        ->andReturnTrue();

    $draft = new Newsletter;
    $draft->status = NewsletterStatusEnum::Draft;

    $sent = new Newsletter;
    $sent->status = NewsletterStatusEnum::Sent;

    $policy = new NewsletterPolicy;

    expect($policy->send($user, $draft))->toBeTrue()
        ->and($policy->send($user, $sent))->toBeFalse();
});

it('wires the custom send action to policy authorization', function (): void {
    $newsletter = new Newsletter;
    $newsletter->status = NewsletterStatusEnum::Draft;

    Gate::shouldReceive('allows')
        ->once()
        ->with('send', $newsletter)
        ->andReturnFalse();

    expect(SendNewsletterTableAction::make()->record($newsletter)->isAuthorized())->toBeFalse();
});
