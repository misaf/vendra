<?php

declare(strict_types=1);

use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraStore\Jobs\CompleteStoreProvisioningJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraSupport\Context\ContextKeys;
use Misaf\VendraSupport\Context\RequestJobContext;
use Misaf\VendraSupport\Tenancy\Events\TenantProvisioned;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;
use Misaf\VendraTenant\Jobs\CacheTenantRoutesJob;

beforeEach(function (): void {
    Queue::fake([CacheTenantRoutesJob::class]);
});

it('queues replacement provisioning attempts while preventing concurrent processing', function (): void {
    Queue::fake();
    $job = new CompleteStoreProvisioningJob(123);

    CompleteStoreProvisioningJob::dispatch(123);
    CompleteStoreProvisioningJob::dispatch(123);

    Queue::assertPushedTimes(CompleteStoreProvisioningJob::class, 2);

    expect($job->middleware())
        ->toHaveCount(1)
        ->and($job->middleware()[0])->toBeInstanceOf(WithoutOverlapping::class);
});

it('completes provisioning checkpoints before activating the tenant', function (): void {
    Event::fake([TenantProvisioned::class]);
    $tenant = Store::factory()->create([
        'active' => false,
        'provisioning_status' => TenantProvisioningStatus::Pending,
        'provisioning_should_seed' => true,
        'provisioned_at' => null,
    ]);

    (new CompleteStoreProvisioningJob($tenant->id))->handle();

    $tenant->refresh();

    expect($tenant->active)->toBeTrue()
        ->and($tenant->provisioning_status)->toBe(TenantProvisioningStatus::Ready)
        ->and($tenant->provisioning_seeded_at)->not->toBeNull()
        ->and($tenant->routes_cached_at)->not->toBeNull()
        ->and($tenant->provisioned_at)->not->toBeNull()
        ->and($tenant->provisioning_failed_at)->toBeNull()
        ->and($tenant->provisioning_error)->toBeNull();

    Event::assertDispatched(
        TenantProvisioned::class,
        fn (TenantProvisioned $event): bool => $event->tenant->is($tenant) && $event->shouldSeed,
    );

    (new CompleteStoreProvisioningJob($tenant->id))->handle();

    Queue::assertPushedTimes(CacheTenantRoutesJob::class, 1);
});

it('records a failed checkpoint and safely retries unfinished work', function (): void {
    $tenant = Store::factory()->create([
        'active' => false,
        'provisioning_status' => TenantProvisioningStatus::Pending,
        'provisioning_should_seed' => true,
        'provisioned_at' => null,
    ]);
    Event::listen(TenantProvisioned::class, fn (): never => throw new RuntimeException('Seeder failed.'));
    $job = new CompleteStoreProvisioningJob($tenant->id);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Seeder failed.');

    expect($tenant->refresh()->active)->toBeFalse()
        ->and($tenant->provisioning_status)->toBe(TenantProvisioningStatus::Failed)
        ->and($tenant->provisioning_seeded_at)->toBeNull()
        ->and($tenant->provisioning_failed_at)->not->toBeNull()
        ->and($tenant->provisioning_error)->toBe('Seeder failed.');

    Event::fake([TenantProvisioned::class]);
    $job->handle();

    expect($tenant->refresh()->active)->toBeTrue()
        ->and($tenant->provisioning_status)->toBe(TenantProvisioningStatus::Ready)
        ->and($tenant->provisioning_seeded_at)->not->toBeNull();
});

it('activates an unsubscribed reseller property under billing suspension', function (): void {
    Event::fake([TenantProvisioned::class]);
    $reseller = Reseller::factory()->create();
    $tenant = Store::factory()->create([
        'reseller_id' => $reseller->id,
        'active' => false,
        'provisioning_status' => TenantProvisioningStatus::Pending,
        'provisioning_should_seed' => false,
        'provisioned_at' => null,
    ]);

    (new CompleteStoreProvisioningJob($tenant->id))->handle();

    expect($tenant->refresh()->active)->toBeTrue()
        ->and($tenant->billing_suspended_at)->not->toBeNull()
        ->and($tenant->provisioning_status)->toBe(TenantProvisioningStatus::Ready);

    Event::assertNotDispatched(TenantProvisioned::class);
});

it('scopes provisioning identifiers without leaking them afterward', function (): void {
    Event::fake([TenantProvisioned::class]);
    $reseller = Reseller::factory()->create();
    $tenant = Store::factory()->create([
        'reseller_id' => $reseller->id,
        'active' => false,
        'provisioning_status' => TenantProvisioningStatus::Pending,
        'provisioning_should_seed' => false,
        'provisioned_at' => null,
    ]);
    $captured = [];

    Store::updated(function (Store $updatedTenant) use (&$captured, $tenant): void {
        if ($updatedTenant->is($tenant)) {
            $captured[] = RequestJobContext::current();
        }
    });
    Context::add(RequestJobContext::OPERATION, 'outer');

    (new CompleteStoreProvisioningJob($tenant->id))->handle();

    expect($captured)->not->toBeEmpty()
        ->and($captured[0]->traceId)->toBeUuid()
        ->and($captured[0]->operation)->toBe('store_provisioning')
        ->and($captured[0]->tenantId)->toBe($tenant->getKey())
        ->and($captured[0]->metadata[ContextKeys::RESELLER_ID])->toBe($reseller->getKey())
        ->and(Context::get(RequestJobContext::OPERATION))->toBe('outer')
        ->and(Context::has(RequestJobContext::TENANT_ID))->toBeFalse();
});
