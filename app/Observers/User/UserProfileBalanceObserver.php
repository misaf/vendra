<?php

declare(strict_types=1);

namespace App\Observers\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class UserProfileBalanceObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\User\UserProfileBalance $userProfileBalance): void {}

    public function deleted(\App\Models\User\UserProfileBalance $userProfileBalance): void {}

    public function forceDeleted(\App\Models\User\UserProfileBalance $userProfileBalance): void {}

    public function restored(\App\Models\User\UserProfileBalance $userProfileBalance): void {}

    public function updated(\App\Models\User\UserProfileBalance $userProfileBalance): void {}
}
