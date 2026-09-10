<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Queue;
use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraStore\Actions\OffboardStoreAction;
use Misaf\VendraStore\Actions\ReactivateStoreAction;
use Misaf\VendraStore\Actions\RedeployStoreStorefrontAction;
use Misaf\VendraStore\Actions\RestoreOffboardedStoreAction;
use Misaf\VendraStore\Actions\RetryFailedStorefrontDeploymentAction;
use Misaf\VendraStore\Actions\RetryStoreProvisioningAction;
use Misaf\VendraStore\Actions\SuspendStoreAction;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Jobs\CompleteStoreProvisioningJob;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Models\StorefrontDeployment;
use Misaf\VendraSubscription\Exceptions\SubscriptionLimitException;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraSubscription\Models\Subscription;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

beforeEach(function (): void {
    Queue::fake();
});

it('suspends and reactivates a ready store while converging its storefront intent', function (): void {
    $store = Store::factory()->active()->create();
    $deployment = StorefrontDeployment::factory()->for($store)->create([
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    app(SuspendStoreAction::class)->execute($store);

    expect($store->refresh()->status())->toBe(StoreStatus::Suspended)
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

    app(ReactivateStoreAction::class)->execute($store);

    expect($store->refresh()->status())->toBe(StoreStatus::Active)
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Running);

    Queue::assertPushed(ReconcileStorefrontJob::class);
});

it('requeues failed or in-progress store provisioning without bypassing the job', function (): void {
    $failed = Store::factory()->provisioningFailed()->inactive()->create([
        'provisioning_error' => 'Seed failed.',
    ]);
    $processing = Store::factory()->provisioning()->inactive()->create();

    app(RetryStoreProvisioningAction::class)->execute($failed);
    app(RetryStoreProvisioningAction::class)->execute($processing);

    expect($failed->refresh()->provisioning_status)->toBe(TenantProvisioningStatus::Pending)
        ->and($failed->provisioning_error)->toBeNull()
        ->and($processing->refresh()->provisioning_status)->toBe(TenantProvisioningStatus::Processing);

    Queue::assertPushed(CompleteStoreProvisioningJob::class, 2);
});

it('queues storefront redeployment and failed retry through the existing provisioning job', function (): void {
    $deployment = StorefrontDeployment::factory()->create([
        'status' => StorefrontDeploymentStatus::Ready,
        'desired_state' => StorefrontDesiredState::Stopped,
    ]);
    $failed = StorefrontDeployment::factory()->create([
        'status' => StorefrontDeploymentStatus::Failed,
    ]);

    app(RedeployStoreStorefrontAction::class)->execute($deployment);
    app(RetryFailedStorefrontDeploymentAction::class)->execute($failed);

    expect($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Running);

    Queue::assertPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id && $job->force,
    );
    Queue::assertPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $failed->id && ! $job->force,
    );
});

it('offboards and restores a store while preserving its history and stopping its storefront', function (): void {
    $store = Store::factory()->active()->create();
    $domain = StoreDomain::factory()->for($store)->create(['active' => true]);
    $deployment = StorefrontDeployment::factory()->for($store)->create([
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    app(OffboardStoreAction::class)->execute($store, 'Merchant requested closure.');

    $archived = Store::query()->withTrashed()->findOrFail($store->id);

    expect($archived->trashed())->toBeTrue()
        ->and($archived->active)->toBeFalse()
        ->and($archived->metadata('offboarding.reason'))->toBe('Merchant requested closure.')
        ->and($domain->fresh()?->trashed())->toBeTrue()
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

    app(RestoreOffboardedStoreAction::class)->execute($archived);

    expect($archived->refresh()->trashed())->toBeFalse()
        ->and($archived->active)->toBeTrue()
        ->and($archived->metadata('offboarding.restored_at'))->not->toBeNull()
        ->and($domain->refresh()->trashed())->toBeFalse()
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Running);
});

it('revalidates reseller quota before restoring an offboarded store', function (): void {
    $reseller = Reseller::factory()->create();
    Subscription::factory()->forSubscriber($reseller)->for(Plan::factory()->maxUnits(1))->create();
    $archived = Store::factory()->active()->create(['reseller_id' => $reseller->id]);

    app(OffboardStoreAction::class)->execute($archived, 'Temporarily archived.');
    Store::factory()->active()->create(['reseller_id' => $reseller->id]);

    expect(fn () => app(RestoreOffboardedStoreAction::class)->execute($archived))
        ->toThrow(SubscriptionLimitException::class);
});
