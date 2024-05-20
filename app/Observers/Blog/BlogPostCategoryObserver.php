<?php

declare(strict_types=1);

namespace App\Observers\Blog;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

final class BlogPostCategoryObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Blog\BlogPostCategory $blogPostCategory): void {}

    public function deleted(\App\Models\Blog\BlogPostCategory $blogPostCategory): void
    {
        $blogPostCategory->blogPosts()->delete();

        Cache::forget('blog_post_row_count');
    }

    public function forceDeleted(\App\Models\Blog\BlogPostCategory $blogPostCategory): void {}

    public function restored(\App\Models\Blog\BlogPostCategory $blogPostCategory): void {}

    public function updated(\App\Models\Blog\BlogPostCategory $blogPostCategory): void {}
}
