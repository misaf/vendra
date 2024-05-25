<?php

declare(strict_types=1);

namespace App\Models\User\Traits;

use App\Models\User\User;
use App\Models\User\UserProfileDocument;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasUserProfileDocument
{
    /**
     * Get the latest user profile document for the user profile.
     *
     * @return HasOne
     */
    public function latestUserProfileDocument(): HasOne
    {
        return $this->hasOne(UserProfileDocument::class)->latestOfMany();
    }

    /**
     * Get the oldest user profile document for the user profile.
     *
     * @return HasOne
     */
    public function oldestUserProfileDocument(): HasOne
    {
        return $this->hasOne(UserProfileDocument::class)->oldestOfMany();
    }

    /**
     * Get the latest user profile document for the user profile.
     *
     * @return HasMany
     */
    public function userProfileDocuments(): HasMany
    {
        return $this->hasMany(UserProfileDocument::class);
    }
}
