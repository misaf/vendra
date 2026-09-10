<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraPhone\Models\PhoneNumber;
use Misaf\VendraUserProfile\Models\UserProfile;

it('persists phone numbers against the installed user profile', function (): void {
    makeCurrentTestTenant();
    $profile = UserProfile::factory()->forUser(createTestUser())->create();

    $phone = PhoneNumber::factory()->create([
        'user_profile_id' => $profile->id,
        'country_code' => 'DE',
        'number' => '+4930123456',
    ]);

    expect($profile->phoneNumbers())->toBeInstanceOf(HasMany::class)
        ->and($profile->phoneNumbers()->sole()->is($phone))->toBeTrue()
        ->and($phone->tenant_id)->toBe(1);
});

it('uses scalar country-aware columns and structured metadata', function (): void {
    expect(Schema::getColumnType('phone_numbers', 'number'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('phone_numbers', 'metadata'))->toBeIn(['json', 'text']);
});
