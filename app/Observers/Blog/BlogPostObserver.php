<?php

declare(strict_types=1);

namespace App\Observers\Blog;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Cache;

final class BlogPostObserver implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = true;

    public function created(\App\Models\Blog\BlogPost $blogPost): void
    {
        Cache::forget('blog_post_row_count');
    }

    public function deleted(\App\Models\Blog\BlogPost $blogPost): void
    {
        Cache::forget('blog_post_row_count');
    }

    public function forceDeleted(\App\Models\Blog\BlogPost $blogPost): void {}

    public function restored(\App\Models\Blog\BlogPost $blogPost): void {}

    public function updated(\App\Models\Blog\BlogPost $blogPost): void {}
}
