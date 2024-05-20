<?php

declare(strict_types=1);

namespace App\Policies\Blog;

use App\Models\Blog\BlogPost;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class BlogPostPolicy
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
        return $user->can('create-blog-post');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param \App\Models\User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function delete(\App\Models\User $user, BlogPost $blogPost): bool
    {
        return $user->can('delete-blog-post');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function deleteAny(\App\Models\User $user): bool
    {
        return $user->can('delete-any-blog-post');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param \App\Models\User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function forceDelete(\App\Models\User $user, BlogPost $blogPost): bool
    {
        return $user->can('force-delete-blog-post');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function forceDeleteAny(\App\Models\User $user): bool
    {
        return $user->can('force-delete-any-blog-post');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function reorder(\App\Models\User $user): bool
    {
        return $user->can('reorder-blog-post');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param \App\Models\User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function replicate(\App\Models\User $user, BlogPost $blogPost): bool
    {
        return $user->can('replicate-blog-post');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param \App\Models\User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function restore(\App\Models\User $user, BlogPost $blogPost): bool
    {
        return $user->can('restore-blog-post');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param \App\Models\User $user
     * @return bool
     */
    public function restoreAny(\App\Models\User $user): bool
    {
        return $user->can('restore-any-blog-post');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param \App\Models\User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function update(\App\Models\User $user, BlogPost $blogPost): bool
    {
        return $user->can('update-blog-post');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param \App\Models\User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function view(?User $user = null, BlogPost $blogPost): bool
    {
        return true;

        return $user->can('view-blog-post');
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

        return $user->can('view-any-blog-post');
    }

    /**
     * Determine whether the user can view the blog post category model.
     *
     * @param \App\Models\User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function viewBlogPostCategory(?User $user = null, BlogPost $blogPost): bool
    {
        return $this->view($user, $blogPost);
    }

    /**
     * Determine whether the user can view the multimedia model.
     *
     * @param \App\Models\User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function viewMultimedia(?User $user = null, BlogPost $blogPost): bool
    {
        return $this->view($user, $blogPost);
    }
}
