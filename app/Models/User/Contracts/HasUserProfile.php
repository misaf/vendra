<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface HasUserProfile
{
    /**
     * Get the latest user profile for the user.
     *
     * @return HasMany
     */
    public function latestUserProfile(): HasOne;

    /**
     * Get the oldest user profile for the user.
     *
     * @return HasOne
     */
    public function oldestUserProfile(): HasOne;

    /**
     * Get the user that owns the profile.
     *
     * @return HasOne
     */
    public function userProfiles(): HasOne;
}
