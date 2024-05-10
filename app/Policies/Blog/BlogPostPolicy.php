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
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->can('create-blog-post');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function delete(User $user, BlogPost $blogPost): bool
    {
        return $user->can('delete-blog-post');
    }

    /**
     * Determine whether the user can delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete-any-blog-post');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function forceDelete(User $user, BlogPost $blogPost): bool
    {
        return $user->can('force-delete-blog-post');
    }

    /**
     * Determine whether the user can permanently delete any models.
     *
     * @param User $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force-delete-any-blog-post');
    }

    /**
     * Determine whether the user can reorder the model.
     *
     * @param User $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder-blog-post');
    }

    /**
     * Determine whether the user can replicate the model.
     *
     * @param User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function replicate(User $user, BlogPost $blogPost): bool
    {
        return $user->can('replicate-blog-post');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function restore(User $user, BlogPost $blogPost): bool
    {
        return $user->can('restore-blog-post');
    }

    /**
     * Determine whether the user can restore any models.
     *
     * @param User $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore-any-blog-post');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function update(User $user, BlogPost $blogPost): bool
    {
        return $user->can('update-blog-post');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
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
     * @param User $user
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
     * @param User $user
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
     * @param User $user
     * @param BlogPost $blogPost
     * @return bool
     */
    public function viewMultimedia(?User $user = null, BlogPost $blogPost): bool
    {
        return $this->view($user, $blogPost);
    }
}
