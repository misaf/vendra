<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraReseller\Models\Reseller;
use Misaf\VendraStore\Actions\OffboardStoreAction;
use Misaf\VendraStore\Actions\ReactivateStoreAction;
use Misaf\VendraStore\Actions\RedeployStoreStorefrontAction;
use Misaf\VendraStore\Actions\RestartStoreStorefrontAction;
use Misaf\VendraStore\Actions\RestoreOffboardedStoreAction;
use Misaf\VendraStore\Actions\RetryFailedStorefrontDeploymentAction;
use Misaf\VendraStore\Actions\RetryStoreProvisioningAction;
use Misaf\VendraStore\Actions\StartStoreStorefrontAction;
use Misaf\VendraStore\Actions\StopStoreStorefrontAction;
use Misaf\VendraStore\Actions\SuspendStoreAction;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;
use Misaf\VendraStore\Exceptions\StoreNotServingException;
use Misaf\VendraStore\Jobs\CompleteStoreProvisioningJob;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Jobs\RestartStorefrontJob;
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

    resolve(SuspendStoreAction::class)->execute($store);

    expect($store->refresh()->status())->toBe(StoreStatus::Suspended)
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

    resolve(ReactivateStoreAction::class)->execute($store);

    expect($store->refresh()->status())->toBe(StoreStatus::Active)
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Running);

    Queue::assertPushed(ReconcileStorefrontJob::class);
});

it('requeues failed or in-progress store provisioning without bypassing the job', function (): void {
    $failed = Store::factory()->provisioningFailed()->inactive()->create([
        'provisioning_error' => 'Seed failed.',
    ]);
    $processing = Store::factory()->provisioning()->inactive()->create();

    resolve(RetryStoreProvisioningAction::class)->execute($failed);
    resolve(RetryStoreProvisioningAction::class)->execute($processing);

    expect($failed->refresh()->provisioning_status)->toBe(TenantProvisioningStatus::Pending)
        ->and($failed->provisioning_error)->toBeNull()
        ->and($processing->refresh()->provisioning_status)->toBe(TenantProvisioningStatus::Processing);

    Queue::assertPushed(CompleteStoreProvisioningJob::class, 2);
});

it('queues storefront redeployment and failed retry through the existing provisioning job', function (): void {
    $deployment = StorefrontDeployment::factory()->for(Store::factory()->active())->create([
        'status' => StorefrontDeploymentStatus::Ready,
        'desired_state' => StorefrontDesiredState::Stopped,
    ]);
    $failed = StorefrontDeployment::factory()->for(Store::factory()->active())->create([
        'status' => StorefrontDeploymentStatus::Failed,
    ]);

    resolve(RedeployStoreStorefrontAction::class)->execute($deployment);
    resolve(RetryFailedStorefrontDeploymentAction::class)->execute($failed);

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

    resolve(OffboardStoreAction::class)->execute($store, 'Merchant requested closure.');

    $archived = Store::query()->withTrashed()->findOrFail($store->id);

    expect($archived->trashed())->toBeTrue()
        ->and($archived->active)->toBeFalse()
        ->and($archived->metadata('offboarding.reason'))->toBe('Merchant requested closure.')
        ->and($domain->fresh()?->trashed())->toBeTrue()
        ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

    resolve(RestoreOffboardedStoreAction::class)->execute($archived);

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

    resolve(OffboardStoreAction::class)->execute($archived, 'Temporarily archived.');
    Store::factory()->active()->create(['reseller_id' => $reseller->id]);

    expect(fn () => resolve(RestoreOffboardedStoreAction::class)->execute($archived))
        ->toThrow(SubscriptionLimitException::class);
});

it('refuses to operate on an offboarded store it locks', function (string $action): void {
    $store = Store::factory()->active()->create();
    $store->delete();

    expect(fn (): Store => resolve($action)->execute($store))->toThrow(ModelNotFoundException::class)
        ->and(Store::withTrashed()->findOrFail($store->getKey())->active)->toBeTrue();

    Queue::assertNothingPushed();
})->with([
    'suspend' => SuspendStoreAction::class,
    'reactivate' => ReactivateStoreAction::class,
    'retry provisioning' => RetryStoreProvisioningAction::class,
]);

describe('storefront intent', function (): void {
    it('records intent and queues the runtime work for start, stop, and restart', function (): void {
        $store = Store::factory()->active()->create();
        $deployment = StorefrontDeployment::factory()->for($store)->create([
            'status' => StorefrontDeploymentStatus::Ready,
            'desired_state' => StorefrontDesiredState::Running,
        ]);

        resolve(StopStoreStorefrontAction::class)->execute($deployment);
        expect($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

        resolve(StartStoreStorefrontAction::class)->execute($deployment);
        expect($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Running);

        resolve(RestartStoreStorefrontAction::class)->execute($deployment);

        Queue::assertPushed(ReconcileStorefrontJob::class, fn (ReconcileStorefrontJob $job): bool => $job->deploymentId === $deployment->id);
        Queue::assertPushed(RestartStorefrontJob::class, fn (RestartStorefrontJob $job): bool => $job->deploymentId === $deployment->id);
    });

    it('refuses to run the storefront of a suspended or offboarded store', function (string $action, Store $store): void {
        $deployment = StorefrontDeployment::factory()->for($store)->create([
            'status' => StorefrontDeploymentStatus::Failed,
            'desired_state' => StorefrontDesiredState::Stopped,
        ]);

        expect(fn () => resolve($action)->execute($deployment))->toThrow(StoreNotServingException::class)
            ->and($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

        Queue::assertNothingPushed();
    })->with([
        'start' => StartStoreStorefrontAction::class,
        'restart' => RestartStoreStorefrontAction::class,
        'redeploy' => RedeployStoreStorefrontAction::class,
        'retry failed' => RetryFailedStorefrontDeploymentAction::class,
    ])->with([
        'suspended' => fn (): Store => Store::factory()->active()->suspended()->create(),
        'offboarded' => function (): Store {
            $store = Store::factory()->active()->create();
            $store->delete();

            return $store;
        },
    ]);

    it('does not bring up a storefront whose intent changed to stopped before a queued deploy ran', function (StorefrontDeploymentStatus $status, StorefrontDeploymentStatus $expected): void {
        $deployment = StorefrontDeployment::factory()->create([
            'status' => $status,
            'desired_state' => StorefrontDesiredState::Stopped,
        ]);
        $provisioner = Mockery::mock(StorefrontProvisioner::class);
        $provisioner->shouldNotReceive('provision');
        app()->instance(StorefrontProvisioner::class, $provisioner);

        app()->call([new ProvisionStorefrontJob($deployment->id, force: true), 'handle']);

        expect($deployment->refresh()->status)->toBe($expected)
            ->and($deployment->desired_state)->toBe(StorefrontDesiredState::Stopped);
    })->with([
        'retry left processing' => [StorefrontDeploymentStatus::Processing, StorefrontDeploymentStatus::Failed],
        'not yet started' => [StorefrontDeploymentStatus::Pending, StorefrontDeploymentStatus::Pending],
    ]);

    it('skips failed deployments meant to stay stopped when retrying failed storefronts', function (): void {
        $running = StorefrontDeployment::factory()->create([
            'status' => StorefrontDeploymentStatus::Failed,
            'desired_state' => StorefrontDesiredState::Running,
        ]);
        StorefrontDeployment::factory()->create([
            'status' => StorefrontDeploymentStatus::Failed,
            'desired_state' => StorefrontDesiredState::Stopped,
        ]);

        $this->artisan('storefront:retry-failed')->assertSuccessful();

        Queue::assertPushed(ProvisionStorefrontJob::class, 1);
        Queue::assertPushed(ProvisionStorefrontJob::class, fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $running->id);
    });
});

it('refuses to suspend a store that is still provisioning', function (): void {
    $store = Store::factory()->provisioning()->inactive()->create();

    expect(fn () => resolve(SuspendStoreAction::class)->execute($store))->toThrow(LogicException::class);

    Queue::assertNothingPushed();
});
