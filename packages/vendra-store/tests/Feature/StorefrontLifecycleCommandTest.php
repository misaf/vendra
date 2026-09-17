<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StorefrontDeployment;

beforeEach(function (): void {
    Config::set('container.drivers.docker.host', 'unix:///var/run/docker.sock');
});

it('records start intent and leaves the runtime work to the queue', function (): void {
    Queue::fake();

    $deployment = StorefrontDeployment::factory()->for(Store::factory()->active())->create([
        'slug' => 'acme-flowers',
        'desired_state' => StorefrontDesiredState::Stopped,
    ]);

    $this->artisan('storefront:lifecycle', ['action' => 'start', 'slug' => 'acme-flowers'])
        ->expectsOutput('Storefront [acme-flowers] start queued.')
        ->assertSuccessful();

    expect($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Running);

    Queue::assertPushed(ReconcileStorefrontJob::class);
});

it('records stop intent so the storefront stays down through reconciliation', function (): void {
    Queue::fake();

    $deployment = StorefrontDeployment::factory()->for(Store::factory()->active())->create([
        'slug' => 'acme-flowers',
        'desired_state' => StorefrontDesiredState::Running,
    ]);

    $this->artisan('storefront:lifecycle', ['action' => 'stop', 'slug' => 'acme-flowers'])
        ->expectsOutput('Storefront [acme-flowers] stop queued.')
        ->assertSuccessful();

    expect($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

    Queue::assertPushed(ReconcileStorefrontJob::class);
});

it('fails when no deployment carries the given slug', function (): void {
    $this->artisan('storefront:lifecycle', ['action' => 'status', 'slug' => 'missing-store'])
        ->expectsOutput('No storefront deployment named [missing-store] exists.')
        ->assertFailed();
});

it('refuses to start a storefront whose store may not serve', function (): void {
    Queue::fake();

    $store = Store::factory()->suspended()->create();
    $deployment = StorefrontDeployment::factory()->for($store)->create([
        'slug' => 'acme-flowers',
        'desired_state' => StorefrontDesiredState::Stopped,
    ]);

    $this->artisan('storefront:lifecycle', ['action' => 'start', 'slug' => 'acme-flowers'])
        ->assertFailed();

    expect($deployment->refresh()->desired_state)->toBe(StorefrontDesiredState::Stopped);

    Queue::assertNothingPushed();
});

it('reports the recorded and observed state side by side', function (): void {
    StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'status' => StorefrontDeploymentStatus::Ready,
        'desired_state' => StorefrontDesiredState::Running,
        'container_name' => 'vendra-storefront-acme-flowers',
    ]);

    fakeExistingStorefront();

    $this->artisan('storefront:lifecycle', ['action' => 'status', 'slug' => 'acme-flowers'])
        ->expectsOutputToContain('acme-flowers')
        ->expectsOutputToContain('Runtime state')
        ->assertSuccessful();
});

it('prints the storefront logs the provisioner returns', function (): void {
    StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'status' => StorefrontDeploymentStatus::Ready,
        'container_name' => 'vendra-storefront-acme-flowers',
    ]);

    fakeExistingStorefront(logs: 'storefront listening on :3000');

    $this->artisan('storefront:lifecycle', ['action' => 'logs', 'slug' => 'acme-flowers'])
        ->expectsOutputToContain('storefront listening on :3000')
        ->assertSuccessful();
});

it('rejects an action it does not know', function (): void {
    StorefrontDeployment::factory()->create(['slug' => 'acme-flowers']);

    $this->artisan('storefront:lifecycle', ['action' => 'nuke', 'slug' => 'acme-flowers'])
        ->expectsOutput('Unknown action. Use start, stop, restart, status, or logs.')
        ->assertFailed();
});
