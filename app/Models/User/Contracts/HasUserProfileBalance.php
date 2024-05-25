<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface HasUserProfileBalance
{
    /**
     * Get the latest user profile balance for the user profile.
     *
     * @return HasOne
     */
    public function latestUserProfileBalance(): HasOne;

    /**
     * Get the oldest use profile balance for the user profile.
     *
     * @return HasOne
     */
    public function oldestUserProfileBalance(): HasOne;

    /**
     * Get the use profile balance for the user profile.
     *
     * @return HasOne
     */
    public function userProfileBalances(): HasMany;
}
