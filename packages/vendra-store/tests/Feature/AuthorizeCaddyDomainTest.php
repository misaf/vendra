<?php

declare(strict_types=1);

use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;

it('authorizes certificates for an active tenant domain from localhost', function (): void {
    $tenant = Store::factory()->active()->create();

    StoreDomain::factory()->for($tenant)->create([
        'name' => 'shop.example.com',
        'active' => true,
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->get('/caddy/domain-check?domain=shop.example.com')
        ->assertSuccessful();
});

it('authorizes canonical and custom admin domains from localhost', function (string $domain): void {
    config()->set('app.url', 'https://vendra.test');
    config()->set('vendra-tenant.central_host', 'vendra.test');

    $tenant = Store::factory()->active()->create(['slug' => 'acme']);

    StoreDomain::factory()->for($tenant)->create([
        'name' => 'acme.example.com',
        'active' => true,
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->get('/caddy/domain-check?domain='.urlencode($domain))
        ->assertSuccessful();
})->with([
    'canonical admin domain' => 'acme.admin.vendra.test',
    'custom admin domain' => 'admin.acme.example.com',
]);

it('rejects domains that are not eligible for certificates', function (Closure $createDomain, string $domain): void {
    $createDomain();

    $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
        ->get('/caddy/domain-check?domain='.urlencode($domain))
        ->assertNotFound();
})->with([
    'unknown domain' => [fn (): null => null, 'unknown.example.com'],
    'invalid domain' => [fn (): null => null, 'https://shop.example.com'],
    'inactive domain' => [function (): void {
        $tenant = Store::factory()->active()->create();

        StoreDomain::factory()->for($tenant)->create([
            'name' => 'disabled.example.com',
            'active' => false,
        ]);
    }, 'disabled.example.com'],
    'inactive tenant' => [function (): void {
        $tenant = Store::factory()->inactive()->create();

        StoreDomain::factory()->for($tenant)->create([
            'name' => 'inactive.example.com',
            'active' => true,
        ]);
    }, 'inactive.example.com'],
]);

it('does not expose certificate authorization publicly', function (): void {
    $tenant = Store::factory()->active()->create();

    StoreDomain::factory()->for($tenant)->create([
        'name' => 'shop.example.com',
        'active' => true,
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
        ->get('/caddy/domain-check?domain=shop.example.com')
        ->assertNotFound();
});
