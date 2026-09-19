<?php

declare(strict_types=1);

namespace Misaf\VendraSocialite\Models;

use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser as BaseSocialiteUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * The base finders are overridden because they use `self`, which bypasses tenancy.
 */
final class SocialiteUser extends BaseSocialiteUser
{
    use BelongsToTenant;

    public static function findForProvider(string $provider, SocialiteUserContract $oauthUser): ?self
    {
        return self::query()
            ->where('provider', $provider)
            ->where('provider_id', $oauthUser->getId())
            ->first();
    }

    public static function createForProvider(string $provider, SocialiteUserContract $oauthUser, Authenticatable $user): self
    {
        return self::query()->create([
            'user_id' => $user->getKey(),
            'provider' => $provider,
            'provider_id' => $oauthUser->getId(),
        ]);
    }
}
