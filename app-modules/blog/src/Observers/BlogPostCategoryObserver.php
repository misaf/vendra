<?php

declare(strict_types=1);

namespace Termehsoft\Blog\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Termehsoft\Blog\Models\BlogPostCategory;

final class BlogPostCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the BlogPostCategory "created" event.
     *
     * @param BlogPostCategory $blogPostCategory
     */
    public function created(BlogPostCategory $blogPostCategory): void {}

    /**
     * Handle the BlogPostCategory "deleted" event.
     *
     * @param BlogPostCategory $blogPostCategory
     */
    public function deleted(BlogPostCategory $blogPostCategory): void
    {
        $blogPostCategory->blogPosts()->delete();

        Cache::forget('blog_post_row_count');
    }

    /**
     * Handle the BlogPostCategory "force deleted" event.
     *
     * @param BlogPostCategory $blogPostCategory
     */
    public function forceDeleted(BlogPostCategory $blogPostCategory): void {}

    /**
     * Handle the BlogPostCategory "restored" event.
     *
     * @param BlogPostCategory $blogPostCategory
     */
    public function restored(BlogPostCategory $blogPostCategory): void {}

    /**
     * Handle the BlogPostCategory "updated" event.
     *
     * @param BlogPostCategory $blogPostCategory
     */
    public function updated(BlogPostCategory $blogPostCategory): void {}
}
