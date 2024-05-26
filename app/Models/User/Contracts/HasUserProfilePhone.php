<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method HasOne latestUserProfilePhone
 * @method HasOne oldestUserProfilePhone
 * @method HasMany userProfilePhones
 */
interface HasUserProfilePhone
{
    /**
     * Get the latest user profile phone for the user profile.
     *
     * @return HasOne
     */
    public function latestUserProfilePhone(): HasOne;

    /**
     * Get the oldest user profile phone for the user profile.
     *
     * @return HasOne
     */
    public function oldestUserProfilePhone(): HasOne;

    /**
     * Get the latest user profile phone for the user profile.
     *
     * @return HasMany
     */
    public function userProfilePhones(): HasMany;
}
