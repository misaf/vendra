<?php

declare(strict_types=1);

namespace Termehsoft\User\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Termehsoft\User\Models\UserProfileBalance;

trait HasUserProfileBalance
{
    /**
     * Get the latest user profile balance for the user profile.
     *
     * @return HasOne
     */
    public function latestUserProfileBalance(): HasOne
    {
        return $this->hasOne(UserProfileBalance::class)->latestOfMany();
    }

    /**
     * Get the oldest use profile balance for the user profile.
     *
     * @return HasOne
     */
    public function oldestUserProfileBalance(): HasOne
    {
        return $this->hasOne(UserProfileBalance::class)->oldestOfMany();
    }

    /**
     * Get the use profile balance for the user profile.
     *
     * @return HasOne
     */
    public function userProfileBalances(): HasMany
    {
        return $this->hasMany(UserProfileBalance::class);
    }
}
