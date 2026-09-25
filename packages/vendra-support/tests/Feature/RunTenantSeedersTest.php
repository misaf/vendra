<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Misaf\VendraSupport\Contracts\TenantResolver;
use Misaf\VendraSupport\Tenancy\Events\TenantProvisioned;
use Misaf\VendraSupport\Tenancy\Listeners\RunTenantSeeders;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraSupport\Tests\Feature\SupportTestSeedCommand;
use Misaf\VendraSupport\Tests\Feature\SupportTestTenantSeeder;

beforeEach(function (): void {
    SupportTestTenantSeeder::$seededTenants = [];

    $seeders = new TenantSeeders;
    $seeders->register(SupportTestSeedCommand::class);
    app()->instance(TenantSeeders::class, $seeders);
});

it('runs registered seeders inside the provisioned tenant without their Artisan command', function (): void {
    $previous = makeCurrentTestTenant();
    $provisioned = createTestTenant();

    if ($previous === null || $provisioned === null) {
        $this->markTestSkipped('Tenancy is disabled.');
    }

    expect(Artisan::all())->not->toHaveKey('support-test:seed');

    resolve(RunTenantSeeders::class)->handle(new TenantProvisioned($provisioned, shouldSeed: true));

    expect(SupportTestTenantSeeder::$seededTenants)->toBe([$provisioned->getKey()])
        ->and(resolve(TenantResolver::class)->currentId())->toBe($previous->getKey());
});

it('seeds nothing when provisioning skips seeding', function (): void {
    $tenant = createTestTenant();

    if ($tenant === null) {
        $this->markTestSkipped('Tenancy is disabled.');
    }

    resolve(RunTenantSeeders::class)->handle(new TenantProvisioned($tenant));

    expect(SupportTestTenantSeeder::$seededTenants)->toBeEmpty();
});
