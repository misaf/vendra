<?php

declare(strict_types=1);

namespace Misaf\VendraPhone\Observers;

use Misaf\VendraSupport\Observers\Concerns\MaintainsSingleFlagPerOwner;

/**
 * Keep exactly one primary phone number per user profile. Synchronous, because
 * the flag is adjusted before the write.
 */
final class PhoneNumberObserver
{
    use MaintainsSingleFlagPerOwner;

    protected function flagColumn(): string
    {
        return 'is_primary';
    }

    protected function ownerColumn(): string
    {
        return 'user_profile_id';
    }
}
