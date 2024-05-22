<?php

declare(strict_types=1);

namespace App\Observers\User;

use App\Models\User\UserProfileBalance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class UserProfileBalanceObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the UserProfileBalance "created" event.
     *
     * @param UserProfileBalance $userProfileBalance
     * @return void
     */
    public function created(UserProfileBalance $userProfileBalance): void {}

    /**
     * Handle the UserProfileBalance "deleted" event.
     *
     * @param UserProfileBalance $userProfileBalance
     * @return void
     */
    public function deleted(UserProfileBalance $userProfileBalance): void {}

    /**
     * Handle the UserProfileBalance "force deleted" event.
     *
     * @param UserProfileBalance $userProfileBalance
     * @return void
     */
    public function forceDeleted(UserProfileBalance $userProfileBalance): void {}

    /**
     * Handle the restored "created" event.
     *
     * @param UserProfileBalance $userProfileBalance
     * @return void
     */
    public function restored(UserProfileBalance $userProfileBalance): void {}

    /**
     * Handle the updated "created" event.
     *
     * @param UserProfileBalance $userProfileBalance
     * @return void
     */
    public function updated(UserProfileBalance $userProfileBalance): void {}
}
