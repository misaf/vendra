<?php

declare(strict_types=1);

use Misaf\VendraProduct\Database\Factories\ProductCategoryFactory;
use Misaf\VendraProduct\Database\Factories\ProductFactory;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraStore\Models\StoreDomain;

beforeEach(function (): void {
    config()->set('app.url', 'https://vendra.test');
    config()->set('vendra-tenant.central_host', 'vendra.test');
});

it('rejects API resource requests that do not resolve a tenant', function (): void {
    $this->getJson('https://vendra.test/api/catalog/products', [
        'Accept' => 'application/vnd.api+json',
    ])->assertNotFound();
});

it('resolves the tenant on the canonical API host from the storefront origin', function (): void {
    $tenant = Store::factory()->active()->create(['slug' => 'flowers']);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'flowers.example.com',
        'active' => true,
    ]);

    $tenant->execute(function (): void {
        $category = ProductCategoryFactory::new()->active()->create();
        ProductFactory::new()->forCategory($category)->create([
            'name' => ['en' => 'Storefront Rose'],
        ]);
    });

    forgetCurrentTestTenant();

    $this->getJson('https://api.vendra.test/api/catalog/products', [
        'Accept' => 'application/vnd.api+json',
        'Accept-Language' => 'en',
        'Origin' => 'https://flowers.example.com',
    ])
        ->assertSuccessful()
        ->assertJsonPath('meta.totalItems', 1)
        ->assertJsonPath('data.0.attributes.name.en', 'Storefront Rose');

    expect(currentTestTenant())->toBeNull();
});

it('falls back to the referer when the canonical API host receives no origin', function (): void {
    $tenant = Store::factory()->active()->create(['slug' => 'flowers']);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'flowers.example.com',
        'active' => true,
    ]);

    forgetCurrentTestTenant();

    $this->getJson('https://api.vendra.test/api/catalog/products', [
        'Accept' => 'application/vnd.api+json',
        'Referer' => 'https://flowers.example.com/en/products',
    ])->assertSuccessful();
});

it('rejects a canonical API request whose origin is not an active tenant domain', function (): void {
    $tenant = Store::factory()->active()->create(['slug' => 'flowers']);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'flowers.example.com',
        'active' => false,
    ]);

    forgetCurrentTestTenant();

    $this->getJson('https://api.vendra.test/api/catalog/products', [
        'Accept' => 'application/vnd.api+json',
        'Origin' => 'https://flowers.example.com',
    ])->assertNotFound();

    $this->getJson('https://api.vendra.test/api/catalog/products', [
        'Accept' => 'application/vnd.api+json',
        'Origin' => 'https://attacker.example.com',
    ])->assertNotFound();
});

it('does not accept an origin on host shapes other than the canonical API', function (): void {
    $tenant = Store::factory()->active()->create(['slug' => 'flowers']);
    StoreDomain::factory()->for($tenant)->create([
        'name' => 'flowers.example.com',
        'active' => true,
    ]);

    forgetCurrentTestTenant();

    $this->getJson('https://vendra.test/api/catalog/products', [
        'Accept' => 'application/vnd.api+json',
        'Origin' => 'https://flowers.example.com',
    ])->assertNotFound();
});

it('resolves and isolates API resources by tenant domain', function (): void {
    $firstTenant = Store::factory()->active()->create(['slug' => 'flowers']);
    StoreDomain::factory()->for($firstTenant)->create([
        'name' => 'flowers.example.com',
        'active' => true,
    ]);

    $firstTenant->execute(function (): void {
        $category = ProductCategoryFactory::new()->active()->create();
        ProductFactory::new()->forCategory($category)->create([
            'name' => ['en' => 'Visible Rose'],
        ]);
    });

    $secondTenant = Store::factory()->active()->create(['slug' => 'other']);
    $secondTenant->execute(function (): void {
        $category = ProductCategoryFactory::new()->active()->create();
        ProductFactory::new()->forCategory($category)->create([
            'name' => ['en' => 'Hidden Tulip'],
        ]);
    });

    forgetCurrentTestTenant();

    $this->getJson('https://admin.flowers.example.com/api/catalog/products', [
        'Accept' => 'application/vnd.api+json',
        'Accept-Language' => 'en',
    ])
        ->assertSuccessful()
        ->assertJsonPath('meta.totalItems', 1)
        ->assertJsonPath('data.0.attributes.name.en', 'Visible Rose')
        ->assertJsonMissing(['en' => 'Hidden Tulip']);

    expect(currentTestTenant())->toBeNull();
});
