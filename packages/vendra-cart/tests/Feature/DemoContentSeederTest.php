<?php

declare(strict_types=1);

use Misaf\VendraCart\Database\Seeders\DemoContentSeeder;
use Misaf\VendraCart\Models\Cart;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    $carts = Cart::query()->count();

    expect($carts)->toBeGreaterThan(0);

    resolve(DemoContentSeeder::class)->run();

    expect(Cart::query()->count())->toBe($carts);
});
