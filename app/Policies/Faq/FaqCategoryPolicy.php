<?php

declare(strict_types=1);

namespace App\Policies\Faq;

use App\Models\Faq\FaqCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class FaqCategoryPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return $user->can('create-faq-category');
    }

    public function delete(User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('delete-faq-category');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-faq-category');
    }

    public function forceDelete(User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('force-delete-faq-category');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-faq-category');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder-faq-category');
    }

    public function replicate(User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('replicate-faq-category');
    }

    public function restore(User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('restore-faq-category');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-faq-category');
    }

    public function update(User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('update-faq-category');
    }

    public function view(?User $user, FaqCategory $faqCategory): bool
    {
        return true;

        return $user->can('view-faq-category');
    }

    public function viewAny(?User $user): bool
    {
        return true;

        return $user->can('view-any-faq-category');
    }

    public function viewFaqs(?User $user, FaqCategory $faqCategory)
    {
        return $this->view($user, $faqCategory);
    }

    public function viewMultimedia(?User $user, FaqCategory $faqCategory)
    {
        return $this->view($user, $faqCategory);
    }
}
