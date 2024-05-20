<?php

declare(strict_types=1);

namespace App\Observers\User;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

final class UserProfilePhoneObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\User\UserProfilePhone $userProfilePhone): void {}

    public function deleted(\App\Models\User\UserProfilePhone $userProfilePhone): void {}

    public function forceDeleted(\App\Models\User\UserProfilePhone $userProfilePhone): void {}

    public function restored(\App\Models\User\UserProfilePhone $userProfilePhone): void {}

    public function updated(\App\Models\User\UserProfilePhone $userProfilePhone): void {}
}
