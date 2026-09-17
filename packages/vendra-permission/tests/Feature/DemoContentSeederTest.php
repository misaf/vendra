<?php

declare(strict_types=1);

use Misaf\VendraPermission\Database\Seeders\DemoContentSeeder;
use Misaf\VendraPermission\Models\Role;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    $roles = Role::query()->count();

    expect($roles)->toBeGreaterThan(0);

    resolve(DemoContentSeeder::class)->run();

    expect(Role::query()->count())->toBe($roles);
});
