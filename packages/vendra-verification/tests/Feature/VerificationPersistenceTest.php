<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Misaf\VendraUserProfile\Models\UserProfile;
use Misaf\VendraVerification\Models\Verification;

it('persists verifications against the installed user profile', function (): void {
    makeCurrentTestTenant();
    $profile = UserProfile::factory()->forUser(createTestUser())->create();

    $verification = Verification::factory()->create([
        'user_profile_id' => $profile->id,
        'country_code' => 'AE',
        'provider' => 'Example KYC',
    ]);

    expect($profile->verifications())->toBeInstanceOf(HasMany::class)
        ->and($profile->verifications()->sole()->is($verification))->toBeTrue()
        ->and($verification->tenant_id)->toBe(1);
});

it('uses scalar country-aware columns and structured metadata', function (): void {
    expect(Schema::getColumnType('verifications', 'country_code'))->toBeIn(['string', 'varchar'])
        ->and(Schema::getColumnType('verifications', 'metadata'))->toBeIn(['json', 'text']);
});
