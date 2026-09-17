<?php

declare(strict_types=1);

use Illuminate\Bus\UniqueLock;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Contracts\StorefrontProvisioner;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

beforeEach(function (): void {
    Config::set('container.drivers.docker.host', 'unix:///var/run/docker.sock');
});

/**
 * Hold the unique lock the way a job already in flight does.
 */
function holdProvisionLockFor(StorefrontDeployment $deployment): void
{
    new UniqueLock(cache()->driver())->acquire(new ProvisionStorefrontJob($deployment->id, force: true));
}

it('reports only the deployments the bus actually accepted', function (): void {
    Queue::fake();

    $queued = StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'desired_state' => StorefrontDesiredState::Running,
    ]);
    $inFlight = StorefrontDeployment::factory()->create([
        'slug' => 'busy-florist',
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    holdProvisionLockFor($inFlight);

    $this->artisan('storefront:redeploy')
        ->expectsOutputToContain('1 storefront deployment(s) queued for redeployment.')
        ->expectsOutputToContain('1 skipped, already being provisioned: busy-florist')
        ->assertSuccessful();

    expect($queued->fresh())->not->toBeNull();
});

/*
 | The run that cost an afternoon: every dispatch discarded, and the command
 | still reporting the whole estate as queued.
 */
it('does not claim to have queued anything when every dispatch was discarded', function (): void {
    Queue::fake();

    $deployment = StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    holdProvisionLockFor($deployment);

    $this->artisan('storefront:redeploy')
        ->expectsOutputToContain('0 storefront deployment(s) queued for redeployment.')
        ->expectsOutputToContain('1 skipped, already being provisioned: acme-flowers')
        ->assertSuccessful();
});

it('says nothing about skipping when every dispatch was accepted', function (): void {
    Queue::fake();

    StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    $this->artisan('storefront:redeploy')
        ->expectsOutputToContain('1 storefront deployment(s) queued for redeployment.')
        ->doesntExpectOutputToContain('skipped')
        ->assertSuccessful();
});

it('breaks a stale lock only when asked to', function (): void {
    Queue::fake();

    $deployment = StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    holdProvisionLockFor($deployment);

    $this->artisan('storefront:redeploy --force-unique')
        ->expectsOutputToContain('1 storefront deployment(s) queued for redeployment.')
        ->doesntExpectOutputToContain('skipped')
        ->assertSuccessful();

    Queue::assertPushed(ProvisionStorefrontJob::class);
});

it('offers the escape hatch on every dispatching command', function (): void {
    foreach (['storefront:redeploy', 'storefront:retry-failed', 'storefront:reconcile'] as $command) {
        expect(Artisan::all()[$command]->getDefinition()->hasOption('force-unique'))
            ->toBeTrue("[{$command}] should accept --force-unique");
    }
});

it('keeps a sync pass going past a failed deployment and reports it', function (): void {
    StorefrontDeployment::factory()->create(['slug' => 'broken-one', 'desired_state' => StorefrontDesiredState::Running]);
    StorefrontDeployment::factory()->create(['slug' => 'broken-two', 'desired_state' => StorefrontDesiredState::Running]);
    bindFakeDockerEngine(fn () => dockerResponse(['message' => 'daemon down'], 500));

    $this->artisan('storefront:reconcile --sync')
        ->expectsOutputToContain('0 storefront deployment(s) reconciled.')
        ->assertFailed();
});

it('skips a sync deployment a worker already holds the lock for', function (): void {
    $inFlight = StorefrontDeployment::factory()->create(['slug' => 'busy-florist', 'desired_state' => StorefrontDesiredState::Running]);
    holdProvisionLockFor($inFlight);
    $provisioner = Mockery::mock(StorefrontProvisioner::class);
    $provisioner->shouldNotReceive('provision');
    app()->instance(StorefrontProvisioner::class, $provisioner);

    $this->artisan('storefront:redeploy --sync')
        ->expectsOutputToContain('1 skipped')
        ->assertSuccessful();
});
