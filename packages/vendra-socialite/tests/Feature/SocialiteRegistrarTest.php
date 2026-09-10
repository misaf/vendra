<?php

declare(strict_types=1);

use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Misaf\VendraSocialite\Support\SocialiteRegistrar;
use Misaf\VendraUser\Models\User;

beforeEach(function (): void {
    makeCurrentTestTenant();
});

/**
 * @param  array{id?: string, nickname?: ?string, name?: ?string, email?: ?string}  $overrides
 */
function fakeOauthUser(array $overrides = []): SocialiteUserContract
{
    $data = array_merge([
        'id' => (string) fake()->unique()->randomNumber(6),
        'nickname' => 'Ada.Lovelace',
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.test',
    ], $overrides);

    $oauthUser = Mockery::mock(SocialiteUserContract::class);
    $oauthUser->shouldReceive('getId')->andReturn($data['id']);
    $oauthUser->shouldReceive('getNickname')->andReturn($data['nickname']);
    $oauthUser->shouldReceive('getName')->andReturn($data['name']);
    $oauthUser->shouldReceive('getEmail')->andReturn($data['email']);

    return $oauthUser;
}

it('derives a schema-valid username from an oauth seed', function (): void {
    expect(SocialiteRegistrar::generateUsername('Ada.Lovelace'))->toBe('ada-lovelace')
        ->and(SocialiteRegistrar::generateUsername(''))->toBe('user')
        ->and(SocialiteRegistrar::generateUsername('ab'))->toBe('user')
        ->and(SocialiteRegistrar::generateUsername('A_very_long_display_name'))->toHaveLength(12);
});

it('suffixes the username when the base is already taken', function (): void {
    User::factory()->create(['username' => 'ada-lovelace']);

    $username = SocialiteRegistrar::generateUsername('Ada.Lovelace');

    expect($username)->not->toBe('ada-lovelace')
        ->and(User::query()->where('username', $username)->exists())->toBeFalse();
});

it('creates a verified user mapped onto the module schema from an oauth identity', function (): void {
    $user = SocialiteRegistrar::createUserUsing('google', fakeOauthUser(), FilamentSocialitePlugin::make());

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->email)->toBe('ada@example.test')
        ->and($user->username)->toBe('ada-lovelace')
        ->and($user->email_verified_at)->not->toBeNull()
        ->and($user->tenant_id)->not->toBeNull();
});
