<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraAddress\Models\Address;
use Misaf\VendraUserProfile\Models\UserProfile;

it('persists addresses against the installed user profile', function (): void {
    makeCurrentTestTenant();
    $profile = UserProfile::factory()->forUser(createTestUser())->create();

    $address = Address::factory()->create([
        'user_profile_id' => $profile->id,
        'country_code' => 'JP',
        'administrative_area' => 'Tokyo',
        'metadata' => ['building' => 'North Tower'],
    ]);

    expect($profile->addresses())->toBeInstanceOf(HasMany::class)
        ->and($profile->addresses()->sole()->is($address))->toBeTrue()
        ->and($address->tenant_id)->toBe(1);
});

it('uses scalar country-aware columns and structured metadata', function (): void {
    expect(Schema::getColumnType('addresses', 'country_code'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('addresses', 'metadata'))->toBeIn(['json', 'text']);
});
