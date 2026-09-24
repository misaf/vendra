<?php

declare(strict_types=1);

use Illuminate\Database\UniqueConstraintViolationException;
use Misaf\VendraPhone\Models\PhoneNumber;
use Misaf\VendraUserProfile\Models\UserProfile;

beforeEach(function (): void {
    makeCurrentTestTenant();

    $this->profile = UserProfile::factory()->forUser(createTestUser())->create();
});

it('makes a profile first number its primary number', function (): void {
    $first = PhoneNumber::factory()->forUserProfile($this->profile)->create();
    $second = PhoneNumber::factory()->forUserProfile($this->profile)->create();

    expect($first->refresh()->is_primary)->toBeTrue()
        ->and($second->refresh()->is_primary)->toBeFalse();
});

it('keeps a single primary when another number is flagged', function (): void {
    $first = PhoneNumber::factory()->forUserProfile($this->profile)->create();
    $second = PhoneNumber::factory()->forUserProfile($this->profile)->create();

    $second->update(['is_primary' => true]);

    expect($first->refresh()->is_primary)->toBeFalse()
        ->and($second->refresh()->is_primary)->toBeTrue();
});

it('leaves other profiles primary numbers alone', function (): void {
    $other = PhoneNumber::factory()->forUserProfile(UserProfile::factory()->forUser(createTestUser())->create())->create();

    PhoneNumber::factory()->forUserProfile($this->profile)->create(['is_primary' => true]);

    expect($other->refresh()->is_primary)->toBeTrue();
});

it('refuses to unflag the only primary', function (): void {
    $address = PhoneNumber::factory()->forUserProfile($this->profile)->create();

    $address->update(['is_primary' => false]);

    expect($address->refresh()->is_primary)->toBeTrue();
});

it('hands the primary flag to the oldest remaining number when it is deleted', function (): void {
    $first = PhoneNumber::factory()->forUserProfile($this->profile)->create();
    $second = PhoneNumber::factory()->forUserProfile($this->profile)->create();
    $third = PhoneNumber::factory()->forUserProfile($this->profile)->create();

    $first->delete();

    expect(PhoneNumber::withTrashed()->find($first->id)->is_primary)->toBeFalse()
        ->and($second->refresh()->is_primary)->toBeTrue()
        ->and($third->refresh()->is_primary)->toBeFalse();
});

it('rejects a second primary number in the database', function (): void {
    PhoneNumber::factory()->forUserProfile($this->profile)->create();
    $second = PhoneNumber::factory()->forUserProfile($this->profile)->create();

    $second->forceFill(['is_primary' => true])->saveQuietly();
})->throws(UniqueConstraintViolationException::class);
