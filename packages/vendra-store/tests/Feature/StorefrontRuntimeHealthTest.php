<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\RecordStorefrontRuntimeHealthAction;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Jobs\RecordStorefrontRuntimeHealthJob;
use Misaf\VendraStore\Support\StorefrontRuntimeHealth;
use Misaf\VendraStore\Support\StorefrontRuntimeHealthReport;
use Spatie\Multitenancy\Jobs\NotTenantAware;

beforeEach(function (): void {
    Config::set('container.drivers.docker.host', 'http://runtime-health.test');
    Config::set('vendra-store.storefront.network', 'traefik-public');
});

it('records a reachable runtime and its storefront network for the panels', function (): void {
    fakeDockerEngine(serverHeader: 'Docker/27.0.0 (linux)');

    resolve(RecordStorefrontRuntimeHealthAction::class)->execute();

    $report = resolve(StorefrontRuntimeHealth::class)->latest();

    expect($report)->toBeInstanceOf(StorefrontRuntimeHealthReport::class)
        ->and($report->status->reachable)->toBeTrue()
        ->and($report->networkName)->toBe('traefik-public')
        ->and($report->network?->driver)->toBe('bridge')
        ->and($report->isHealthy())->toBeTrue();
});

it('records a missing storefront network as unhealthy', function (): void {
    fakeDockerEngine(networkExists: false);

    $report = resolve(RecordStorefrontRuntimeHealthAction::class)->execute();

    expect($report->status->reachable)->toBeTrue()
        ->and($report->network)->toBeNull()
        ->and($report->isHealthy())->toBeFalse();
});

it('records an unreachable runtime without checking the network', function (): void {
    $transport = bindFakeDockerEngine(fn ($request, bool $stream) => dockerResponse(['message' => 'The runtime is down.'], 500));

    $report = resolve(RecordStorefrontRuntimeHealthAction::class)->execute();

    expect($report->status->reachable)->toBeFalse()
        ->and($report->isHealthy())->toBeFalse()
        ->and(collect($transport->requests)->contains(fn ($request): bool => str_contains($request->path, '/networks/')))->toBeFalse();
});

it('reads the report back from a cache that only unserializes allow-listed classes', function (): void {
    fakeDockerEngine();
    Config::set('cache.default', 'array');
    Config::set('cache.stores.array.serialize', true);
    Config::set('cache.serializable_classes', []);
    Cache::forgetDriver('array');

    resolve(RecordStorefrontRuntimeHealthAction::class)->execute();

    expect(resolve(StorefrontRuntimeHealth::class)->latest()?->network?->name)->toBe('traefik-public');
});

it('treats a report the worker stopped refreshing as stale', function (): void {
    fakeDockerEngine();

    resolve(RecordStorefrontRuntimeHealthAction::class)->execute();

    $this->travel(StorefrontRuntimeHealthReport::STALE_AFTER_SECONDS + 1)->seconds();

    $report = resolve(StorefrontRuntimeHealth::class)->latest();

    expect($report?->isStale())->toBeTrue()
        ->and($report?->isHealthy())->toBeFalse();
});

it('has no report before the worker records one', function (): void {
    expect(resolve(StorefrontRuntimeHealth::class)->latest())->toBeNull();
});

it('probes the runtime on the storefront queue outside tenancy', function (): void {
    Queue::fake();

    dispatch(new RecordStorefrontRuntimeHealthJob);

    Queue::assertPushedOn(ProvisionStorefrontJob::QUEUE, RecordStorefrontRuntimeHealthJob::class);

    expect(new RecordStorefrontRuntimeHealthJob)->toBeInstanceOf(NotTenantAware::class);
});
