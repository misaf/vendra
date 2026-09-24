<?php

declare(strict_types=1);

use Misaf\VendraAddress\Models\Address;
use Misaf\VendraUserProfile\Models\UserProfile;

beforeEach(function (): void {
    makeCurrentTestTenant();

    $this->profile = UserProfile::factory()->forUser(createTestUser())->create();
});

it('makes a profile first address its default', function (): void {
    $first = Address::factory()->forUserProfile($this->profile)->create();
    $second = Address::factory()->forUserProfile($this->profile)->create();

    expect($first->refresh()->is_default)->toBeTrue()
        ->and($second->refresh()->is_default)->toBeFalse();
});

it('keeps a single default when another address is flagged', function (): void {
    $first = Address::factory()->forUserProfile($this->profile)->create();
    $second = Address::factory()->forUserProfile($this->profile)->create();

    $second->update(['is_default' => true]);

    expect($first->refresh()->is_default)->toBeFalse()
        ->and($second->refresh()->is_default)->toBeTrue();
});

it('leaves other profiles defaults alone', function (): void {
    $other = Address::factory()->forUserProfile(UserProfile::factory()->forUser(createTestUser())->create())->create();

    Address::factory()->forUserProfile($this->profile)->create(['is_default' => true]);

    expect($other->refresh()->is_default)->toBeTrue();
});

it('refuses to unflag the only default', function (): void {
    $address = Address::factory()->forUserProfile($this->profile)->create();

    $address->update(['is_default' => false]);

    expect($address->refresh()->is_default)->toBeTrue();
});

it('hands the default to the oldest remaining address when it is deleted', function (): void {
    $first = Address::factory()->forUserProfile($this->profile)->create();
    $second = Address::factory()->forUserProfile($this->profile)->create();
    $third = Address::factory()->forUserProfile($this->profile)->create();

    $first->delete();

    expect(Address::withTrashed()->find($first->id)->is_default)->toBeFalse()
        ->and($second->refresh()->is_default)->toBeTrue()
        ->and($third->refresh()->is_default)->toBeFalse();
});
