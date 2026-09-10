<?php

declare(strict_types=1);

namespace Misaf\VendraSocialite\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser as BaseSocialiteUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Misaf\VendraSupport\Tenancy\BelongsToTenant;

/**
 * Tenant-aware socialite identity. Extends the package model so it maps to the
 * tenant-scoped `socialite_users` table, and overrides the base finders so
 * lookups and inserts honour the tenant scope (the base uses `self`, which
 * resolves to the parent class and would bypass tenancy).
 */
final class SocialiteUser extends BaseSocialiteUser
{
    use HasFactory;
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
