<?php

declare(strict_types=1);

use Misaf\VendraConsole\Models\Console;
use Misaf\VendraSubscription\Models\Plan;
use Misaf\VendraTransaction\Facades\TransactionGatewayRegistry;
use Misaf\VendraTransaction\Services\TransactionGatewayRegistry as TransactionGatewayRegistryClass;

it('seeds console access, the platform gateway, and lets the plan observer assign the default plan', function (): void {
    $this->artisan('db:seed', ['--force' => true, '--no-interaction' => true])
        ->assertSuccessful();

    expect(Plan::query()->active()->default()->sole()->slug)->toBe('free')
        ->and(Plan::query()->count())->toBe(3)
        ->and(Console::query()->active()->sole()->user->username)->toBe('console')
        ->and(TransactionGatewayRegistry::hasActive(TransactionGatewayRegistryClass::INTERNAL_GATEWAY_SLUG))->toBeTrue();
});
