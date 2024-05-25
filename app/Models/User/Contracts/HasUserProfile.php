<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface HasUserProfile
{
    /**
     * Get the latest document for the user profile.
     *
     * @return HasMany
     */
    public function userProfiles(): HasMany;
}
