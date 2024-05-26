<?php

declare(strict_types=1);

namespace App\Models\User\Contracts;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method HasOne latestUserProfileDocument
 * @method HasOne oldestUserProfileDocument
 * @method HasMany userProfileDocuments
 */
interface HasUserProfileDocument
{
    /**
     * Get the latest user profile document for the user profile.
     *
     * @return HasOne
     */
    public function latestUserProfileDocument(): HasOne;

    /**
     * Get the oldest user profile document for the user profile.
     *
     * @return HasOne
     */
    public function oldestUserProfileDocument(): HasOne;

    /**
     * Get the latest user profile document for the user profile.
     *
     * @return HasMany
     */
    public function userProfileDocuments(): HasMany;
}
