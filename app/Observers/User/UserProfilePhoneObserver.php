<?php

declare(strict_types=1);

namespace App\Observers\User;

use App\Models\User\UserProfilePhone;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class UserProfilePhoneObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the UserProfilePhone "created" event.
     *
     * @param UserProfilePhone $userProfilePhone
     * @return void
     */
    public function created(UserProfilePhone $userProfilePhone): void {}

    /**
     * Handle the UserProfilePhone "deleted" event.
     *
     * @param UserProfilePhone $userProfilePhone
     * @return void
     */
    public function deleted(UserProfilePhone $userProfilePhone): void {}

    /**
     * Handle the UserProfilePhone "force deleted" event.
     *
     * @param UserProfilePhone $userProfilePhone
     * @return void
     */
    public function forceDeleted(UserProfilePhone $userProfilePhone): void {}

    /**
     * Handle the UserProfilePhone "restored" event.
     *
     * @param UserProfilePhone $userProfilePhone
     * @return void
     */
    public function restored(UserProfilePhone $userProfilePhone): void {}

    /**
     * Handle the UserProfilePhone "updated" event.
     *
     * @param UserProfilePhone $userProfilePhone
     * @return void
     */
    public function updated(UserProfilePhone $userProfilePhone): void {}
}
