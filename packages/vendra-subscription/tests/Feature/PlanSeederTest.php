<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Misaf\VendraSubscription\Database\Seeders\PlanSeeder;
use Misaf\VendraSubscription\Models\Plan;

it('seeds the default plans idempotently', function (): void {
    Artisan::call('db:seed', ['--class' => PlanSeeder::class, '--force' => true]);
    Artisan::call('db:seed', ['--class' => PlanSeeder::class, '--force' => true]);

    expect(Plan::query()->count())->toBe(3)
        ->and(Plan::query()->where('slug', 'pro')->sole()->allows('priority_support'))->toBeTrue();
});
