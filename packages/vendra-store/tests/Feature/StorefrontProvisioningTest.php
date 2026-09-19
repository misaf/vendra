<?php

declare(strict_types=1);

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Misaf\VendraStore\Actions\RequestStorefrontDeploymentAction;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Jobs\ProvisionStorefrontJob;
use Misaf\VendraStore\Jobs\ReconcileStorefrontJob;
use Misaf\VendraStore\Models\StorefrontDeployment;

beforeEach(function (): void {
    Queue::fake();

    // Covers the built configuration and commands; container provisioning has its own file.
    Config::set('container.drivers.docker.host', 'unix:///var/run/docker.sock');
});

it('keeps an unconfigured deployment pending instead of pretending it succeeded', function (): void {
    Config::set('container.drivers.docker.host', '');
    $tenant = createTestTenant();

    $deployment = resolve(RequestStorefrontDeploymentAction::class)->execute(
        $tenant,
        'acme.test',
        storefrontRequestData(),
    );

    expect($deployment->status)->toBe(StorefrontDeploymentStatus::Pending)
        ->and(Arr::get($deployment->configuration, 'name.en'))->toBe('Acme Flowers')
        ->and(Arr::get($deployment->configuration, 'priceCurrency'))->toBe('IRR')
        ->and(Arr::get($deployment->configuration, 'address.country'))->toBe('IR');
    Queue::assertNothingPushed();
});

it('queues provisioning when the provider is configured', function (): void {
    $tenant = createTestTenant();

    $deployment = resolve(RequestStorefrontDeploymentAction::class)->execute(
        $tenant,
        'acme.test',
        storefrontRequestData(),
    );

    Queue::assertPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $deployment->id,
    );
});

it('reconciles every database deployment including ready storefronts', function (): void {
    $deployments = StorefrontDeployment::factory()->count(2)->sequence(
        ['status' => StorefrontDeploymentStatus::Pending],
        ['status' => StorefrontDeploymentStatus::Ready],
    )->create();

    $this->artisan('vendra-store:reconcile')
        ->expectsOutput('2 storefront deployment(s) queued for reconciliation.')
        ->assertSuccessful();

    foreach ($deployments as $deployment) {
        Queue::assertPushed(
            ReconcileStorefrontJob::class,
            fn (ReconcileStorefrontJob $job): bool => $job->deploymentId === $deployment->id,
        );
    }
});

it('lists the database-backed storefront fleet', function (): void {
    StorefrontDeployment::factory()->create([
        'slug' => 'beta-flowers',
        'domain' => 'beta.test',
        'status' => StorefrontDeploymentStatus::Ready,
        'container_name' => 'container-beta',
        'image_digest' => 'sha256:beta',
    ]);
    StorefrontDeployment::factory()->create([
        'slug' => 'alpha-flowers',
        'domain' => 'alpha.test',
        'status' => StorefrontDeploymentStatus::Pending,
    ]);

    $this->artisan('vendra-store:status')
        ->expectsTable(
            ['Slug', 'Domain', 'Status', 'Desired', 'Container', 'Image digest'],
            [
                ['alpha-flowers', 'alpha.test', 'pending', 'running', '—', '—'],
                ['beta-flowers', 'beta.test', 'ready', 'running', 'container-beta', 'sha256:beta'],
            ],
        )
        ->assertSuccessful();
});

it('retries only failed storefront deployments', function (): void {
    $failedDeployment = StorefrontDeployment::factory()->create([
        'status' => StorefrontDeploymentStatus::Failed,
    ]);
    $readyDeployment = StorefrontDeployment::factory()->create([
        'status' => StorefrontDeploymentStatus::Ready,
    ]);

    $this->artisan('vendra-store:retry-failed')
        ->expectsOutput('1 failed storefront deployment(s) queued for retry.')
        ->assertSuccessful();

    Queue::assertPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $failedDeployment->id,
    );
    Queue::assertNotPushed(
        ProvisionStorefrontJob::class,
        fn (ProvisionStorefrontJob $job): bool => $job->deploymentId === $readyDeployment->id,
    );
});

it('carries per-locale message overrides into the encoded configuration', function (): void {
    $tenant = createTestTenant();

    $deployment = resolve(RequestStorefrontDeploymentAction::class)->execute(
        $tenant,
        'acme.test',
        [...storefrontRequestData(), 'storefront_messages' => [
            'en' => ['products' => ['title' => 'Our Breads']],
            'fa' => ['products' => ['title' => 'نان‌های ما']],
        ]],
    );

    expect(Arr::get($deployment->configuration, 'messages.en.products.title'))->toBe('Our Breads')
        ->and(Arr::get($deployment->configuration, 'messages.fa.products.title'))->toBe('نان‌های ما');
});

it('omits messages entirely when none are supplied', function (): void {
    Config::set('container.drivers.docker.host', '');
    $tenant = createTestTenant();

    $deployment = resolve(RequestStorefrontDeploymentAction::class)->execute($tenant, 'acme.test', storefrontRequestData());

    expect($deployment->configuration)->not->toHaveKey('messages');
});

it('drops malformed message overrides rather than shipping a configuration the storefront rejects', function (): void {
    Config::set('container.drivers.docker.host', '');
    $tenant = createTestTenant();

    $deployment = resolve(RequestStorefrontDeploymentAction::class)->execute(
        $tenant,
        'acme.test',
        [...storefrontRequestData(), 'storefront_messages' => [
            'en' => ['products' => ['title' => 'Kept']],
            'bad' => 'not-an-array',
            'fa' => [],
        ]],
    );

    expect(Arr::get($deployment->configuration, 'messages'))->toBe(['en' => ['products' => ['title' => 'Kept']]]);
});
