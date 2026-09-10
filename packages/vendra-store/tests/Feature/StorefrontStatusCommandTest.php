<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Models\StorefrontDeployment;

beforeEach(function (): void {
    Config::set('container.drivers.docker.host', 'unix:///var/run/docker.sock');
});

function storefrontStatusRuntime(bool $present = false): object
{
    return fakeExistingStorefront(present: $present);
}

it('reports only what the database recorded by default', function (): void {
    StorefrontDeployment::factory()->create(['slug' => 'acme-flowers']);

    $runtime = storefrontStatusRuntime();

    $this->artisan('storefront:status')->assertSuccessful();

    expect($runtime->transport->requests)->toBeEmpty();
});

it('asks the runtime what it actually has when told to', function (): void {
    StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'status' => StorefrontDeploymentStatus::Ready,
        'desired_state' => StorefrontDesiredState::Running,
        'container_name' => 'vendra-storefront-acme-flowers',
    ]);

    storefrontStatusRuntime(present: true);

    $this->artisan('storefront:status --runtime')
        ->doesntExpectOutputToContain('The runtime has no container for')
        ->assertSuccessful();
});

/*
 | Changing CONTAINER_DRIVER or DOCKER_HOST leaves the previous daemon's
 | containers running, unmanaged and invisible, while every deployment row still
 | reads as ready. Absent-but-wanted is the only symptom available, so it is
 | reported as a failure rather than printed in a column and left to be noticed.
 */
it('reports storefronts the runtime has nothing for', function (): void {
    StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'status' => StorefrontDeploymentStatus::Ready,
        'desired_state' => StorefrontDesiredState::Running,
        'container_name' => 'vendra-storefront-acme-flowers',
    ]);

    storefrontStatusRuntime();

    $this->artisan('storefront:status --runtime')
        ->expectsOutputToContain('The runtime has no container for: acme-flowers')
        ->assertFailed();
});

it('does not report a storefront that is absent because it is meant to be', function (): void {
    StorefrontDeployment::factory()->create([
        'slug' => 'acme-flowers',
        'status' => StorefrontDeploymentStatus::Ready,
        'desired_state' => StorefrontDesiredState::Stopped,
    ]);

    storefrontStatusRuntime();

    $this->artisan('storefront:status --runtime')
        ->doesntExpectOutputToContain('The runtime has no container for')
        ->assertSuccessful();
});

it('still prints the recorded state when the runtime will not answer', function (): void {
    StorefrontDeployment::factory()->create(['slug' => 'acme-flowers']);

    bindFakeDockerEngine(fn ($request, bool $stream) => $stream
        ? dockerStreamResponse('', 500)
        : dockerResponse(['message' => 'The fake runtime is configured as unreachable.'], 500));

    $this->artisan('storefront:status --runtime')
        ->expectsOutputToContain('acme-flowers')
        ->assertSuccessful();
});
