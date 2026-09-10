<?php

declare(strict_types=1);

use Misaf\VendraSubscription\Database\Seeders\PlanSeeder;
use Misaf\VendraSubscription\Models\Plan;

it('seeds the default plans idempotently', function (): void {
    (new PlanSeeder)->run();
    (new PlanSeeder)->run();

    expect(Plan::query()->count())->toBe(3)
        ->and(Plan::query()->where('slug', 'pro')->sole()->allows('priority_support'))->toBeTrue();
});
