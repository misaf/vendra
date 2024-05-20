<?php

declare(strict_types=1);

namespace App\Policies\Faq;

use App\Models\Faq\FaqCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class FaqCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(\App\Models\User $user): bool
    {
        return $user->can('create-faq-category');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param FaqCategory $faqCategory
     * @return bool
     */
    public function delete(\App\Models\User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('delete-faq-category');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-faq-category');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param FaqCategory $faqCategory
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('force-delete-faq-category');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-faq-category');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param FaqCategory $faqCategory
     * @return bool
     */
    public function replicate(\App\Models\User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('replicate-faq-category');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param FaqCategory $faqCategory
     * @return bool
     */
    public function restore(\App\Models\User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('restore-faq-category');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-faq-category');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param FaqCategory $faqCategory
     * @return bool
     */
    public function update(\App\Models\User $user, FaqCategory $faqCategory): bool
    {
        return $user->can('update-faq-category');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param FaqCategory $faqCategory
     * @return bool
     */
    public function view(?User $user = null, FaqCategory $faqCategory): bool
    {
        return true;

        return $user->can('view-faq-category');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(?User $user = null): bool
    {
        return true;

        return $user->can('view-any-faq-category');
    }

    /**
     * Determine whether the user can view the faqs model.
     *
     * @param \App\Models\User $user
     * @param FaqCategory $faqCategory
     * @return bool
     */
    public function viewFaqs(?User $user = null, FaqCategory $faqCategory): bool
    {
        return $this->view($user, $faqCategory);
    }

    /**
     * Determine whether the user can view the multimedia model.
     *
     * @param \App\Models\User $user
     * @param FaqCategory $faqCategory
     * @return bool
     */
    public function viewMultimedia(?User $user = null, FaqCategory $faqCategory): bool
    {
        return $this->view($user, $faqCategory);
    }
}
