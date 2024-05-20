<?php

declare(strict_types=1);

namespace App\Policies\Blog;

use App\Models\Blog\BlogPostCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class BlogPostCategoryPolicy
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
        return $user->can('create-blog-post-category');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function delete(\App\Models\User $user, \App\Models\Blog\BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('delete-blog-post-category');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-blog-post-category');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('force-delete-blog-post-category');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-blog-post-category');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function reorder(\App\Models\User $user): bool
    {
        return $user->can('reorder-blog-post-category');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function replicate(\App\Models\User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('replicate-blog-post-category');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function restore(\App\Models\User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('restore-blog-post-category');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-blog-post-category');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function update(\App\Models\User $user, BlogPostCategory $blogPostCategory): bool
    {
        return $user->can('update-blog-post-category');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
     * @param BlogPostCategory $blogPostCategory
     * @return bool
     */
    public function viewMultimedia(?User $user = null, BlogPostCategory $blogPostCategory): bool
    {
        return $this->view($user, $blogPostCategory);
    }
}
