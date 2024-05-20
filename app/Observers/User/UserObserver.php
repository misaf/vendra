<?php

declare(strict_types=1);

namespace App\Observers\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class UserObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\User $user): void {}

    public function deleted(\App\Models\User $user): void
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

    public function forceDeleted(\App\Models\User $user): void {}

    public function restored(\App\Models\User $user): void {}

    public function saved(\App\Models\User $product): void
    {
        Cache::forget('user_row_count');
    }

    public function updated(\App\Models\User $user): void {}
}
