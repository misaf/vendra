<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Tenancy\TenantSeeders;

it('registers its seed command for tenant provisioning', function (): void {
    expect(app(TenantSeeders::class)->ordered())->toContain('vendra-verification:seed');
});

it('seeds its module permissions through the registered seed command', function (): void {
    makeCurrentTestTenant();

    $exitCode = Artisan::call('vendra-verification:seed', [
        'tenant' => 1,
        'seeders' => ['all'],
    ]);

    /** @var class-string<Model> $permissionModel */
    $permissionModel = Config::string('permission.models.permission');

    expect($exitCode)->toBe(0)
        ->and($permissionModel::query()->where('name', 'view-any-verification')->exists())->toBeTrue();
});
