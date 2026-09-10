<?php

declare(strict_types=1);

namespace Misaf\VendraUserProfile\Observers;

use Misaf\VendraUserProfile\Models\UserProfile;

/**
 * Holds the "one default profile per user" invariant.
 *
 * Synchronous: the demotion of the previous default has to land in the same
 * write as the promotion, or a reader between the two sees either two defaults
 * or none.
 */
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
