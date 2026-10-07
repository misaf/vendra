<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\RecordStorefrontRuntimeSnapshotAction;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Jobs\RecordStorefrontRuntimeSnapshotJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraStore\Support\StorefrontRuntimeSnapshots;

beforeEach(function (): void {
    Config::set('queue.default', 'database');
    Config::set('container.drivers.docker.host', 'http://runtime-snapshot.test');
});

it('queues and throttles runtime reads without touching the runtime', function (): void {
    $deployment = StorefrontDeployment::factory()->create();
    $runtime = fakeExistingStorefront();
    Queue::fake([RecordStorefrontRuntimeSnapshotJob::class]);
    $snapshots = resolve(StorefrontRuntimeSnapshots::class);

    expect($snapshots->latest($deployment))->toBeNull()
        ->and($snapshots->latest($deployment))->toBeNull();

    Queue::assertPushedOn(ProvisionStorefrontJob::QUEUE, RecordStorefrontRuntimeSnapshotJob::class, fn (RecordStorefrontRuntimeSnapshotJob $job): bool => $job->deploymentId === $deployment->id && ! $job->logs);
    Queue::assertPushed(RecordStorefrontRuntimeSnapshotJob::class, 1);
    expect($runtime->transport->requests)->toBeEmpty();
});

it('records observation and log snapshots independently on the worker', function (): void {
    $deployment = StorefrontDeployment::factory()->create(['slug' => 'snapshot-store']);
    $runtime = fakeExistingStorefront(logs: 'storefront ready');
    Queue::fake([RecordStorefrontRuntimeSnapshotJob::class]);
    $snapshots = resolve(StorefrontRuntimeSnapshots::class);
    $record = resolve(RecordStorefrontRuntimeSnapshotAction::class);

    new RecordStorefrontRuntimeSnapshotJob($deployment->id)->handle($record);

    expect($snapshots->latest($deployment)?->observation?->containerName)->toBe('vendra-storefront-acme-flowers')
        ->and($runtime->calls)->not->toContain('logs:vendra-storefront-snapshot-store');

    new RecordStorefrontRuntimeSnapshotJob($deployment->id, logs: true)->handle($record);

    expect($snapshots->latest($deployment, logs: true)?->logs)->toBe('storefront ready');
    Queue::assertNothingPushed();
});

it('expires snapshots and queues a new collection', function (): void {
    $deployment = StorefrontDeployment::factory()->create();
    fakeExistingStorefront();
    Queue::fake([RecordStorefrontRuntimeSnapshotJob::class]);
    $snapshots = resolve(StorefrontRuntimeSnapshots::class);

    new RecordStorefrontRuntimeSnapshotJob($deployment->id)->handle(resolve(RecordStorefrontRuntimeSnapshotAction::class));
    expect($snapshots->latest($deployment))->not->toBeNull();

    $this->travel(StorefrontRuntimeSnapshots::TTL_SECONDS + 1)->seconds();

    expect($snapshots->latest($deployment))->toBeNull();
    Queue::assertPushed(RecordStorefrontRuntimeSnapshotJob::class, 1);
});

it('does not show a snapshot after the requested storefront changes', function (): void {
    $deployment = StorefrontDeployment::factory()->create();
    fakeExistingStorefront();
    Queue::fake([RecordStorefrontRuntimeSnapshotJob::class]);
    new RecordStorefrontRuntimeSnapshotJob($deployment->id)->handle(resolve(RecordStorefrontRuntimeSnapshotAction::class));

    $deployment->update(['slug' => 'new-storefront']);

    expect(resolve(StorefrontRuntimeSnapshots::class)->latest($deployment))->toBeNull();
    Queue::assertPushed(RecordStorefrontRuntimeSnapshotJob::class, 1);
});

it('stores primitive snapshots compatible with restricted cache unserialization', function (): void {
    $deployment = StorefrontDeployment::factory()->create();
    fakeExistingStorefront();
    Config::set('cache.default', 'array');
    Config::set('cache.stores.array.serialize', true);
    Config::set('cache.serializable_classes', []);
    Cache::forgetDriver('array');

    new RecordStorefrontRuntimeSnapshotJob($deployment->id)->handle(resolve(RecordStorefrontRuntimeSnapshotAction::class));

    expect(resolve(StorefrontRuntimeSnapshots::class)->latest($deployment)?->observation?->state->value)->toBe('running');
});

it('shares central snapshots across tenant contexts and preserves the current tenant', function (): void {
    Config::set('cache.default', 'database');
    $deployment = StorefrontDeployment::factory()->create(['slug' => 'central-snapshot']);
    fakeExistingStorefront(logs: 'central logs');
    new RecordStorefrontRuntimeSnapshotJob($deployment->id, logs: true)->handle(resolve(RecordStorefrontRuntimeSnapshotAction::class));
    $tenant = Store::factory()->create();
    $tenant->makeCurrent();

    expect(resolve(StorefrontRuntimeSnapshots::class)->latest($deployment, logs: true)?->logs)->toBe('central logs')
        ->and(Store::current()?->getKey())->toBe($tenant->getKey());
});

it('reports runtime failure without treating the storefront as absent', function (): void {
    $deployment = StorefrontDeployment::factory()->create();
    bindFakeDockerEngine(fn ($request, bool $stream) => dockerResponse(['message' => 'Runtime unreachable'], 500));

    new RecordStorefrontRuntimeSnapshotJob($deployment->id)->handle(resolve(RecordStorefrontRuntimeSnapshotAction::class));

    $snapshot = resolve(StorefrontRuntimeSnapshots::class)->latest($deployment);
    expect($snapshot?->observation)->toBeNull()
        ->and($snapshot?->error)->toContain('Runtime unreachable');
});

it('does not fall back to a runtime read with a synchronous queue', function (): void {
    $deployment = StorefrontDeployment::factory()->create();
    $runtime = fakeExistingStorefront();
    Config::set('queue.default', 'sync');
    Queue::fake([RecordStorefrontRuntimeSnapshotJob::class]);

    expect(resolve(StorefrontRuntimeSnapshots::class)->latest($deployment)?->error)->toContain('asynchronous queue')
        ->and($runtime->transport->requests)->toBeEmpty();
    Queue::assertNothingPushed();
});

it('ignores a deployment deleted before collection', function (): void {
    $deployment = StorefrontDeployment::factory()->create();
    $runtime = fakeExistingStorefront();
    $deployment->delete();
    Queue::fake([RecordStorefrontRuntimeSnapshotJob::class]);

    new RecordStorefrontRuntimeSnapshotJob($deployment->id)->handle(resolve(RecordStorefrontRuntimeSnapshotAction::class));

    expect(resolve(StorefrontRuntimeSnapshots::class)->latest($deployment))->toBeNull()
        ->and($runtime->transport->requests)->toBeEmpty();
    Queue::assertNothingPushed();
});

it('records terminal collection failure for the panel', function (): void {
    $deployment = StorefrontDeployment::factory()->create();

    new RecordStorefrontRuntimeSnapshotJob($deployment->id)->failed(new RuntimeException('Worker timed out'));

    $snapshot = resolve(StorefrontRuntimeSnapshots::class)->latest($deployment);
    expect($snapshot?->observation)->toBeNull()
        ->and($snapshot?->error)->toBe('The storefront runtime check failed. Try again shortly.');
});
