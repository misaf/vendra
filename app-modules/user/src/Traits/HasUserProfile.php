<?php

declare(strict_types=1);

namespace Termehsoft\User\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Termehsoft\User\Models\UserProfile;

trait HasUserProfile
{
    /**
     * Get the latest user profile for the user.
     *
     * @return HasMany
     */
    public function latestUserProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class)->latestOfMany();
    }

    /**
     * Get the oldest user profile for the user.
     *
     * @return HasOne
     */
    public function oldestUserProfile(): HasOne
    {
        return $this->hasOne(UserProfile::class)->oldestOfMany();
    }

    /**
     * Get the user that owns the profile.
     *
     * @return HasMany
     */
    public function userProfiles(): HasMany
    {
        return $this->hasMany(UserProfile::class);
    }
}
