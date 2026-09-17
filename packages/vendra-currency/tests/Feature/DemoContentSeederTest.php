<?php

declare(strict_types=1);

use Misaf\VendraCurrency\Database\Seeders\DemoContentSeeder;
use Misaf\VendraCurrency\Models\Currency;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    resolve(DemoContentSeeder::class)->run();

    $currencies = Currency::query()->count();

    expect($currencies)->toBeGreaterThan(0);

    resolve(DemoContentSeeder::class)->run();

    expect(Currency::query()->count())->toBe($currencies);
});
