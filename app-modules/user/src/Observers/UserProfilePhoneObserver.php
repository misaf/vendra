<?php

declare(strict_types=1);

namespace Termehsoft\User\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\User\Models\UserProfilePhone;

final class UserProfilePhoneObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the UserProfilePhone "created" event.
     *
     * @param UserProfilePhone $userProfilePhone
     */
    public function created(UserProfilePhone $userProfilePhone): void {}

    /**
     * Handle the UserProfilePhone "deleted" event.
     *
     * @param UserProfilePhone $userProfilePhone
     */
    public function deleted(UserProfilePhone $userProfilePhone): void {}

    /**
     * Handle the UserProfilePhone "force deleted" event.
     *
     * @param UserProfilePhone $userProfilePhone
     */
    public function forceDeleted(UserProfilePhone $userProfilePhone): void {}

    /**
     * Handle the UserProfilePhone "restored" event.
     *
     * @param UserProfilePhone $userProfilePhone
     */
    public function restored(UserProfilePhone $userProfilePhone): void {}

    /**
     * Handle the UserProfilePhone "updated" event.
     *
     * @param UserProfilePhone $userProfilePhone
     */
    public function updated(UserProfilePhone $userProfilePhone): void {}
}
