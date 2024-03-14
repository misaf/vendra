<?php

declare(strict_types=1);

namespace App\Policies\Faq;

use App\Models\Faq\Faq;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class FaqPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->can('create-faq');
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $user->can('delete-faq');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-faq');
    }

    public function forceDelete(User $user, Faq $faq): bool
    {
        return $user->can('force-delete-faq');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-faq');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder-faq');
    }

    public function replicate(User $user, Faq $faq): bool
    {
        return $user->can('replicate-faq');
    }

    public function restore(User $user, Faq $faq): bool
    {
        return $user->can('restore-faq');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-faq');
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->can('update-faq');
    }

    public function view(?User $user, Faq $faq): bool
    {
        return true;

        return $user->can('view-faq');
    }

    public function viewAny(?User $user): bool
    {
        return true;

        return $user->can('view-any-faq');
    }

    public function viewFaqCategory(?User $user, Faq $faq)
    {
        return $this->view($user, $faq);
    }

    public function viewMultimedia(?User $user, Faq $faq)
    {
        return $this->view($user, $faq);
    }
}
