<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface HasUserProfileDocument
{
    /**
     * Get the latest document for the user profile.
     *
     * @return HasOne
     */
    public function LatestUserProfileDocument(): HasOne;

    /**
     * Get the latest document for the user profile.
     *
     * @return HasOne
     */
    public function userProfileDocuments(): HasMany;
}
