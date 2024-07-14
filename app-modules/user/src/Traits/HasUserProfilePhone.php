<?php

declare(strict_types=1);

namespace Termehsoft\User\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Termehsoft\User\Models\UserProfilePhone;

trait HasUserProfilePhone
{
    /**
     * Get the latest user profile phone for the user profile.
     *
     * @return HasOne
     */
    public function latestUserProfilePhone(): HasOne
    {
        return $this->hasOne(UserProfilePhone::class)->latestOfMany();
    }

    /**
     * Get the oldest user profile phone for the user profile.
     *
     * @return HasOne
     */
    public function oldestUserProfilePhone(): HasOne
    {
        return $this->hasOne(UserProfilePhone::class)->oldestOfMany();
    }

    /**
     * Get the latest user profile phone for the user profile.
     *
     * @return HasMany
     */
    public function userProfilePhones(): HasMany
    {
        return $this->hasMany(UserProfilePhone::class);
    }
}
