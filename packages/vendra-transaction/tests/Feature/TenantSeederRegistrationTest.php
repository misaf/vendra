<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Misaf\VendraSupport\Tenancy\TenantSeeders;
use Misaf\VendraTransaction\Console\Commands\SeedCommand;

it('registers its seed command for tenant provisioning', function (): void {
    expect(resolve(TenantSeeders::class)->ordered())->toContain(SeedCommand::class);
});

it('seeds its module permissions through the registered seed command', function (): void {
    makeCurrentTestTenant();

    $exitCode = Artisan::call(SeedCommand::class, [
        'tenant' => 1,
        'seeders' => ['all'],
    ]);

    /** @var class-string<Model> $permissionModel */
    $permissionModel = Config::string('permission.models.permission');

    expect($exitCode)->toBe(0)
        ->and($permissionModel::query()->where('name', 'view-any-wallet')->exists())->toBeTrue();
});
