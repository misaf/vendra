<?php

declare(strict_types=1);

namespace App\Observers\User;

use App\Models\User\UserProfile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

final class UserProfileObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the UserProfile "created" event.
     *
     * @param UserProfile $userProfile
     * @return void
     */
    public function created(UserProfile $userProfile): void {}

    /**
     * Handle the UserProfile "deleted" event.
     *
     * @param UserProfile $userProfile
     * @return void
     */
    public function deleted(UserProfile $userProfile): void
    {
        DB::transaction(function () use ($userProfile): void {
            $userProfile->userProfileDocuments()->delete();
            $userProfile->userProfilePhones()->delete();
        });
    }

    /**
     * Handle the UserProfile "force deleted" event.
     *
     * @param UserProfile $userProfile
     * @return void
     */
    public function forceDeleted(UserProfile $userProfile): void {}

    /**
     * Handle the UserProfile "restored" event.
     *
     * @param UserProfile $userProfile
     * @return void
     */
    public function restored(UserProfile $userProfile): void {}

    /**
     * Handle the UserProfile "updated" event.
     *
     * @param UserProfile $userProfile
     * @return void
     */
    public function updated(UserProfile $userProfile): void {}
}
