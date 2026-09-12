<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Misaf\VendraStore\Models\Store;
use Misaf\VendraUser\Models\User;

dataset('monitoring gates', ['viewPulse', 'viewHorizon']);

it('allows anyone in the local environment', function (string $gate): void {
    app()->detectEnvironment(fn (): string => 'local');

    expect(Gate::forUser(null)->allows($gate))->toBeTrue();
})->with('monitoring gates');

it('denies guests outside the local environment', function (string $gate): void {
    app()->detectEnvironment(fn (): string => 'production');

    expect(Gate::forUser(null)->allows($gate))->toBeFalse();
})->with('monitoring gates');

it('denies tenant users outside the local environment', function (string $gate): void {
    app()->detectEnvironment(fn (): string => 'production');

    $tenant = Store::factory()->create();
    $tenant->makeCurrent();

    $user = User::factory()->forTenant($tenant)->create();
    Auth::guard('web')->setUser($user);

    expect(Gate::forUser($user)->allows($gate))->toBeFalse();
})->with('monitoring gates');

it('allows console users outside the local environment', function (string $gate): void {
    app()->detectEnvironment(fn (): string => 'production');

    Auth::guard('console')->setUser(User::factory()->create(['tenant_id' => null]));

    expect(Gate::forUser(null)->allows($gate))->toBeTrue();
})->with('monitoring gates');
