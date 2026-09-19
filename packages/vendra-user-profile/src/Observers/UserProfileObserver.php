<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Observers;

use Misaf\VendraUserProfile\Models\UserProfile;

final class UserProfileObserver
{
    public function saving(UserProfile $userProfile): void
    {
        if (! $userProfile->is_default) {
            return;
        }

        UserProfile::query()
            ->where('user_id', $userProfile->user_id)
            ->where('is_default', true)
            ->whereKeyNot($userProfile->getKey())
            ->update(['is_default' => false]);
    }
}
