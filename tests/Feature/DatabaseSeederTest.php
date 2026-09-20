<?php

declare(strict_types=1);

use Misaf\VendraConsole\Models\Console;
use Misaf\VendraSubscription\Models\Plan;

it('seeds console access and lets the plan observer assign the default plan', function (): void {
    $this->artisan('db:seed', ['--force' => true, '--no-interaction' => true])
        ->assertSuccessful();

    expect(Plan::query()->active()->default()->sole()->slug)->toBe('free')
        ->and(Plan::query()->count())->toBe(3)
        ->and(Console::query()->active()->sole()->user->username)->toBe('console');
});
