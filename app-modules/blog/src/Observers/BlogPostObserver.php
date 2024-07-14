<?php

declare(strict_types=1);

namespace Termehsoft\Blog\Observers;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;
use Termehsoft\Blog\Models\BlogPost;

final class BlogPostObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    /**
     * Handle the BlogPost "created" event.
     *
     * @param BlogPost $blogPost
     */
    public function created(BlogPost $blogPost): void
    {
        Cache::forget('blog_post_row_count');
    }

    /**
     * Handle the BlogPost "deleted" event.
     *
     * @param BlogPost $blogPost
     */
    public function deleted(BlogPost $blogPost): void
    {
        Cache::forget('blog_post_row_count');
    }

    /**
     * Handle the BlogPost "force deleted" event.
     *
     * @param BlogPost $blogPost
     */
    public function forceDeleted(BlogPost $blogPost): void {}

    /**
     * Handle the BlogPost "restored" event.
     *
     * @param BlogPost $blogPost
     */
    public function restored(BlogPost $blogPost): void {}

    /**
     * Handle the BlogPost "updated" event.
     *
     * @param BlogPost $blogPost
     */
    public function updated(BlogPost $blogPost): void {}
}
