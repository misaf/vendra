<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraNewsletter\Actions\SendNewsletterAction;
use Misaf\VendraNewsletter\Database\Factories\NewsletterFactory;
use Misaf\VendraNewsletter\Database\Factories\NewsletterSubscriberFactory;
use Misaf\VendraNewsletter\Enums\NewsletterStatusEnum;
use Misaf\VendraNewsletter\Jobs\SendNewsletterBatchJob;
use Misaf\VendraNewsletter\Jobs\SendNewsletterEmailJob;
use Misaf\VendraNewsletter\Mail\NewsletterMail;
use Misaf\VendraNewsletter\Models\Newsletter;
use Misaf\VendraSupport\Context\RequestJobContext;
use Spatie\Multitenancy\Tasks\SwitchRouteCacheTask;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

beforeEach(function (): void {
    config([
        'multitenancy.switch_tenant_tasks' => array_values(array_filter(
            config('multitenancy.switch_tenant_tasks'),
            fn (string $task): bool => $task !== SwitchRouteCacheTask::class,
        )),
    ]);

    makeCurrentTestTenant();
});

it('keeps production demo newsletters from being scheduled automatically', function (): void {
    $fixtures = json_decode(
        File::get(__DIR__.'/../../database/fixtures/demo-content.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect(collect($fixtures)->where('status', NewsletterStatusEnum::Scheduled->value))->toBeEmpty();
});

it('queues a batch job per chunk of subscribers and marks the newsletter as sent', function (): void {
    Queue::fake();

    config(['vendra-newsletter.batch_chunk_size' => 2]);

    $newsletter = NewsletterFactory::new()->draft()->create();
    NewsletterSubscriberFactory::new()->subscribed()->count(5)->create();
    NewsletterSubscriberFactory::new()->unsubscribed()->count(2)->create();

    $queued = resolve(SendNewsletterAction::class)->execute($newsletter);

    expect($queued)->toBe(5)
        ->and($newsletter->refresh()->status)->toBe(NewsletterStatusEnum::Sent)
        ->and($newsletter->sent_at)->not->toBeNull();

    Queue::assertPushed(SendNewsletterBatchJob::class, 3);
});

it('does not resend a newsletter that was already sent', function (): void {
    Queue::fake();

    $newsletter = NewsletterFactory::new()->sent()->create();
    NewsletterSubscriberFactory::new()->subscribed()->count(3)->create();

    $queued = resolve(SendNewsletterAction::class)->execute($newsletter);

    expect($queued)->toBe(0);

    Queue::assertNothingPushed();
});

it('does not resend from a stale model after another process marks the newsletter as sent', function (): void {
    Queue::fake();

    $newsletter = NewsletterFactory::new()->draft()->create();
    NewsletterSubscriberFactory::new()->subscribed()->count(3)->create();

    Newsletter::query()->whereKey($newsletter->id)->update([
        'status' => NewsletterStatusEnum::Sent,
        'sent_at' => now(),
    ]);

    expect(resolve(SendNewsletterAction::class)->execute($newsletter))->toBe(0);

    Queue::assertNothingPushed();
});

it('uses deterministic uniqueness keys for batch and recipient jobs', function (): void {
    $batchJob = new SendNewsletterBatchJob(newsletterId: 10, subscriberIds: [20, 30]);
    $sameBatchJob = new SendNewsletterBatchJob(newsletterId: 10, subscriberIds: [20, 30]);
    $differentBatchJob = new SendNewsletterBatchJob(newsletterId: 10, subscriberIds: [30, 40]);
    $emailJob = new SendNewsletterEmailJob(newsletterId: 10, subscriberId: 20);

    expect($batchJob)->toBeInstanceOf(ShouldBeUnique::class)
        ->and($batchJob->uniqueId())->toBe($sameBatchJob->uniqueId())
        ->and($batchJob->uniqueId())->not->toBe($differentBatchJob->uniqueId())
        ->and($emailJob)->toBeInstanceOf(ShouldBeUnique::class)
        ->and($emailJob->uniqueId())->toBe('10:20');
});

it('enforces unique subscriber emails per tenant and globally unique unsubscribe tokens', function (): void {
    $indexes = collect(Schema::getIndexes('newsletter_subscribers'));

    expect($indexes->contains(
        fn (array $index): bool => Arr::get($index, 'unique') === true && ['tenant_id', 'email'] === Arr::get($index, 'columns'),
    ))->toBeTrue()
        ->and($indexes->contains(
            fn (array $index): bool => Arr::get($index, 'unique') === true && ['unsubscribe_token'] === Arr::get($index, 'columns'),
        ))->toBeTrue();
});

it('enforces one delivery receipt per newsletter recipient', function (): void {
    $indexes = collect(Schema::getIndexes('newsletter_deliveries'));

    expect($indexes->contains(
        fn (array $index): bool => Arr::get($index, 'unique') === true
            && ['newsletter_id', 'newsletter_subscriber_id'] === Arr::get($index, 'columns'),
    ))->toBeTrue();
});

it('fans a batch job out into per-recipient email jobs', function (): void {
    Queue::fake();

    new SendNewsletterBatchJob(newsletterId: 1, subscriberIds: [10, 20, 30])->handle();

    Queue::assertPushed(SendNewsletterEmailJob::class, 3);
});

it('delivers the newsletter mail to a subscribed recipient', function (): void {
    Mail::fake();

    $newsletter = NewsletterFactory::new()->draft()->create();
    $subscriber = NewsletterSubscriberFactory::new()->subscribed()->create();

    new SendNewsletterEmailJob($newsletter->getKey(), $subscriber->getKey())->handle();

    Mail::assertSent(NewsletterMail::class, fn (NewsletterMail $mail): bool => $mail->hasTo($subscriber->email));
});

it('scopes newsletter identifiers without exposing recipient data', function (): void {
    $newsletter = NewsletterFactory::new()->draft()->create();
    $subscriber = NewsletterSubscriberFactory::new()->subscribed()->create();
    $captured = [];

    Mail::shouldReceive('to')
        ->once()
        ->with($subscriber->email)
        ->andReturnSelf();
    Mail::shouldReceive('send')
        ->once()
        ->andReturnUsing(function () use (&$captured): void {
            $captured = Context::all();
        });
    Context::add(RequestJobContext::OPERATION, 'outer');

    new SendNewsletterEmailJob($newsletter->getKey(), $subscriber->getKey())->handle();

    expect($captured)->toMatchArray([
        RequestJobContext::OPERATION => 'newsletter_email',
        'newsletter_id' => $newsletter->getKey(),
        'subscriber_id' => $subscriber->getKey(),
    ])
        ->not->toHaveKeys(['email', 'unsubscribe_token'])
        ->and(Context::get(RequestJobContext::OPERATION))->toBe('outer')
        ->and(Context::has('newsletter_id'))->toBeFalse()
        ->and(Context::has('subscriber_id'))->toBeFalse();
});

it('does not deliver again after a recipient delivery has completed', function (): void {
    Mail::fake();

    $newsletter = NewsletterFactory::new()->draft()->create();
    $subscriber = NewsletterSubscriberFactory::new()->subscribed()->create();
    $job = new SendNewsletterEmailJob($newsletter->getKey(), $subscriber->getKey());

    $job->handle();
    $job->handle();

    Mail::assertSent(NewsletterMail::class, 1);

    assertDatabaseHas('newsletter_deliveries', [
        'newsletter_id' => $newsletter->getKey(),
        'newsletter_subscriber_id' => $subscriber->getKey(),
    ]);

    expect(DB::table('newsletter_deliveries')->value('sent_at'))->not->toBeNull();
});

it('does not record a completed delivery when mail sending fails', function (): void {
    $newsletter = NewsletterFactory::new()->draft()->create();
    $subscriber = NewsletterSubscriberFactory::new()->subscribed()->create();
    $job = new SendNewsletterEmailJob($newsletter->getKey(), $subscriber->getKey());

    Mail::shouldReceive('to')
        ->once()
        ->with($subscriber->email)
        ->andReturnSelf();
    Mail::shouldReceive('send')
        ->once()
        ->andThrow(new RuntimeException('Mail transport failed.'));

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Mail transport failed.');

    assertDatabaseMissing('newsletter_deliveries', [
        'newsletter_id' => $newsletter->getKey(),
        'newsletter_subscriber_id' => $subscriber->getKey(),
    ]);
});

it('skips delivery for an unsubscribed recipient', function (): void {
    Mail::fake();

    $newsletter = NewsletterFactory::new()->draft()->create();
    $subscriber = NewsletterSubscriberFactory::new()->unsubscribed()->create();

    new SendNewsletterEmailJob($newsletter->getKey(), $subscriber->getKey())->handle();

    Mail::assertNothingSent();
});

it('sends scheduled newsletters that are due and leaves future ones untouched', function (): void {
    Queue::fake();

    $due = NewsletterFactory::new()->create([
        'status' => NewsletterStatusEnum::Scheduled,
        'scheduled_at' => now()->subMinute(),
    ]);
    $future = NewsletterFactory::new()->create([
        'status' => NewsletterStatusEnum::Scheduled,
        'scheduled_at' => now()->addDay(),
    ]);
    NewsletterSubscriberFactory::new()->subscribed()->count(3)->create();

    $this->artisan('vendra-newsletter:send-scheduled')->assertSuccessful();

    expect($due->refresh()->status)->toBe(NewsletterStatusEnum::Sent)
        ->and($future->refresh()->status)->toBe(NewsletterStatusEnum::Scheduled);

    Queue::assertPushed(SendNewsletterBatchJob::class, 1);
});

it("scopes each tenant's scheduled send to its own subscribers across all tenants", function (): void {
    Queue::fake();
    forgetCurrentTestTenant();

    $tenantA = createTestTenant();
    $tenantB = createTestTenant();

    /** @var array{0: Newsletter, 1: list<int>} $contextA */
    $contextA = $tenantA->execute(function (): array {
        $newsletter = NewsletterFactory::new()->create([
            'status' => NewsletterStatusEnum::Scheduled,
            'scheduled_at' => now()->subMinute(),
        ]);

        return [$newsletter, NewsletterSubscriberFactory::new()->subscribed()->count(2)->create()->modelKeys()];
    });

    /** @var array{0: Newsletter, 1: list<int>} $contextB */
    $contextB = $tenantB->execute(function (): array {
        $newsletter = NewsletterFactory::new()->create([
            'status' => NewsletterStatusEnum::Scheduled,
            'scheduled_at' => now()->subMinute(),
        ]);

        return [$newsletter, NewsletterSubscriberFactory::new()->subscribed()->count(3)->create()->modelKeys()];
    });

    [$newsletterA, $subscriberIdsA] = $contextA;
    [$newsletterB, $subscriberIdsB] = $contextB;

    $this->artisan('vendra-newsletter:send-scheduled')->assertSuccessful();

    Queue::assertPushed(SendNewsletterBatchJob::class, 2);

    Queue::assertPushed(
        SendNewsletterBatchJob::class,
        fn (SendNewsletterBatchJob $job): bool => $job->newsletterId === $newsletterA->getKey()
            && array_diff($job->subscriberIds, $subscriberIdsA) === []
            && count($job->subscriberIds) === count($subscriberIdsA),
    );

    Queue::assertPushed(
        SendNewsletterBatchJob::class,
        fn (SendNewsletterBatchJob $job): bool => $job->newsletterId === $newsletterB->getKey()
            && array_diff($job->subscriberIds, $subscriberIdsB) === []
            && count($job->subscriberIds) === count($subscriberIdsB),
    );
});
