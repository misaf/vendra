<?php

declare(strict_types=1);

namespace Termehsoft\User\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Termehsoft\User\Models\UserProfileDocument;

final class UserProfileDocumentObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the UserProfileDocument "created" event.
     *
     * @param UserProfileDocument $userProfileDocument
     */
    public function created(UserProfileDocument $userProfileDocument): void {}

    /**
     * Handle the UserProfileDocument "deleted" event.
     *
     * @param UserProfileDocument $userProfileDocument
     */
    public function deleted(UserProfileDocument $userProfileDocument): void {}

    /**
     * Handle the UserProfileDocument "force deleted" event.
     *
     * @param UserProfileDocument $userProfileDocument
     */
    public function forceDeleted(UserProfileDocument $userProfileDocument): void {}

    /**
     * Handle the UserProfileDocument "restored" event.
     *
     * @param UserProfileDocument $userProfileDocument
     */
    public function restored(UserProfileDocument $userProfileDocument): void {}

    /**
     * Handle the UserProfileDocument "updated" event.
     *
     * @param UserProfileDocument $userProfileDocument
     */
    public function updated(UserProfileDocument $userProfileDocument): void {}
}
