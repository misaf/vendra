<?php

declare(strict_types=1);

use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;
use Misaf\VendraStore\Services\StoreDomainFinder;
use Misaf\VendraTenant\Enums\TenantProvisioningStatus;

beforeEach(function (): void {
    config()->set('app.url', 'https://vendra.test');
    config()->set('vendra-tenant.central_host', 'vendra.test');
});

it('resolves canonical and custom admin hosts to the property', function (): void {
    $tenant = Store::factory()->active()->create(['slug' => 'acme']);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'acme.example.com',
        'active' => true,
    ]);

    $tenantFinder = app(StoreDomainFinder::class);

    expect($tenantFinder->findForAdminHost('acme.admin.vendra.test')?->getKey())->toBe($tenant->getKey())
        ->and($tenantFinder->findForAdminHost('admin.acme.example.com')?->getKey())->toBe($tenant->getKey())
        ->and($tenantFinder->findForAdminHost('acme.example.com'))->toBeNull()
        ->and($tenantFinder->findForAdminHost('acme.admin.example.com'))->toBeNull();
});

it('serves the admin login on canonical and custom property hosts', function (string $host): void {
    $tenant = Store::factory()->active()->create(['slug' => 'acme']);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'acme.example.com',
        'active' => true,
    ]);

    $this->get("https://{$host}")->assertRedirect("https://{$host}/login");
    $this->get("https://{$host}/login")->assertSuccessful();
})->with([
    'canonical host' => 'acme.admin.vendra.test',
    'custom host' => 'admin.acme.example.com',
]);

it('serves the canonical admin host after tenant switching changes the application URL', function (): void {
    Store::factory()->active()->create(['slug' => 'acme']);

    config()->set('app.url', 'https://acme.admin.vendra.test');

    $this->get('https://acme.admin.vendra.test/login')->assertSuccessful();
});

it('does not serve the admin panel on the storefront host', function (): void {
    $tenant = Store::factory()->active()->create(['slug' => 'acme']);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'acme.example.com',
        'active' => true,
    ]);

    $this->get('https://acme.example.com/login')->assertNotFound();
});

it('does not resolve manually inactive, billing suspended, or provisioning tenants', function (array $attributes): void {
    $tenant = Store::factory()->active()->create([
        'slug' => 'acme',
        ...$attributes,
    ]);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'acme.example.com',
        'active' => true,
    ]);

    $tenantFinder = app(StoreDomainFinder::class);

    expect($tenantFinder->findForAdminHost('acme.admin.vendra.test'))->toBeNull()
        ->and($tenantFinder->findForAdminHost('admin.acme.example.com'))->toBeNull();
})->with([
    'manual deactivation' => [['active' => false]],
    'billing suspension' => [['billing_suspended_at' => now()]],
    'pending provisioning' => [[
        'provisioning_status' => TenantProvisioningStatus::Pending,
        'provisioned_at' => null,
    ]],
]);
