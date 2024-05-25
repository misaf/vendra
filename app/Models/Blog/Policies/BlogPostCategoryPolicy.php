<?php

declare(strict_types=1);

namespace App\Models\Blog\Policies;

use App\Models\Blog\BlogPostCategory;
use App\Models\User\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class BlogPostCategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-blog-post-category');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function delete(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('delete-blog-post-category');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-blog-post-category');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function forceDelete(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('force-delete-blog-post-category');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-blog-post-category');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param User $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder-blog-post-category');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function replicate(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('replicate-blog-post-category');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function restore(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('restore-blog-post-category');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-blog-post-category');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function update(User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('update-blog-post-category');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function view(?User $user = null, BlogPostCategory $blogPostCategory): bool
    {
        return true;

        return $user->can('view-blog-post-category');
    }

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return bool
     */
    public function viewAny(?User $user = null): bool
    {
        return true;

        return $user->can('view-any-blog-post-category');
    }

    /**
     * Determine whether the user can view the blog posts model.
     *
     * @param User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function viewBlogPosts(?User $user = null, BlogPostCategory $blogPostCategory): bool
    {
        return $this->view($user, $blogPostCategory);
    }

    /**
     * Determine whether the user can view the multimedia model.
     *
     * @param User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function viewMultimedia(?User $user = null, BlogPostCategory $blogPostCategory): bool
    {
        return $this->view($user, $blogPostCategory);
    }
}
