<?php

declare(strict_types=1);

namespace App\Observers\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

final class UserProfileObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\User\UserProfile $userProfile): void {}

    public function deleted(\App\Models\User\UserProfile $userProfile): void
    {
        DB::transaction(function () use ($userProfile): void {
            $userProfile->userProfileDocuments()->delete();
            $userProfile->userProfilePhones()->delete();
        });
    }

    public function forceDeleted(\App\Models\User\UserProfile $userProfile): void {}

    public function restored(\App\Models\User\UserProfile $userProfile): void {}

    public function updated(\App\Models\User\UserProfile $userProfile): void {}
}
