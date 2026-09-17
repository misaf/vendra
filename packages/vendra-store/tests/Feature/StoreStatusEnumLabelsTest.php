<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Lang;
use Misaf\VendraStore\Enums\StorefrontDeploymentStatus;
use Misaf\VendraStore\Enums\StorefrontDesiredState;
use Misaf\VendraStore\Enums\StoreStatus;

it('translates every status badge label in each locale', function (string $locale): void {
    $prefixedEnums = [
        'store_status_' => StoreStatus::cases(),
        'deployment_status_' => StorefrontDeploymentStatus::cases(),
        'desired_state_' => StorefrontDesiredState::cases(),
    ];

    $missing = [];

    foreach ($prefixedEnums as $prefix => $cases) {
        foreach ($cases as $case) {
            if (! Lang::has("vendra-store::enums.{$prefix}{$case->value}", $locale, false)) {
                $missing[] = "{$prefix}{$case->value}";
            }
        }
    }

    expect($missing)->toBeEmpty();
})->with(['en', 'fa', 'de']);

it('colors store statuses by whether the store is serving', function (): void {
    expect(StoreStatus::Active->getColor())->toBe('success')
        ->and(StoreStatus::Suspended->getColor())->toBe('warning')
        ->and(StoreStatus::Failed->getColor())->toBe('danger')
        ->and(StorefrontDeploymentStatus::Ready->getColor())->toBe('success')
        ->and(StorefrontDeploymentStatus::Failed->getColor())->toBe('danger');
});
