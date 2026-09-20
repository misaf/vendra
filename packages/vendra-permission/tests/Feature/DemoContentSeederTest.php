<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Misaf\VendraPermission\Database\Seeders\DemoContentSeeder;
use Misaf\VendraPermission\Models\Role;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    $roles = Role::query()->count();

    expect($roles)->toBeGreaterThan(0);

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    expect(Role::query()->count())->toBe($roles);
});
