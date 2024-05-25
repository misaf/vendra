<?php

declare(strict_types=1);

namespace App\Models\User\Observers;

use App\Models\User\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class UserObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the User "created" event.
     *
     * @param User $user
     */
    public function created(User $user): void {}

    /**
     * Handle the User "deleted" event.
     *
     * @param User $user
     */
    public function deleted(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->userProfiles()->each(function ($userProfile): void {
                $userProfile->userProfileDocuments()->delete();
                $userProfile->userProfilePhones()->delete();

                $userProfile->delete();
            });
        });

        Cache::forget('user_row_count');
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param User $user
     */
    public function forceDeleted(User $user): void {}

    /**
     * Handle the User "restored" event.
     *
     * @param User $user
     */
    public function restored(User $user): void {}

    /**
     * Handle the User "saved" event.
     *
     * @param User $user
     */
    public function saved(User $user): void
    {
        Cache::forget('user_row_count');
    }

    /**
     * Handle the User "updated" event.
     *
     * @param User $user
     */
    public function updated(User $user): void {}
}
