<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Misaf\VendraCurrency\Database\Seeders\DemoContentSeeder;
use Misaf\VendraCurrency\Models\Currency;

it('seeds its demo fixtures again without duplicating rows', function (): void {
    app()->detectEnvironment(fn (): string => 'production');
    makeCurrentTestTenant();

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    $currencies = Currency::query()->count();

    expect($currencies)->toBeGreaterThan(0);

    Artisan::call('db:seed', ['--class' => DemoContentSeeder::class, '--force' => true]);

    expect(Currency::query()->count())->toBe($currencies);
});
