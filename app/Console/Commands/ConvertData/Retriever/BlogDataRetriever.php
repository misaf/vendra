<?php

declare(strict_types=1);

namespace App\Console\Commands\ConvertData\Retriever;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\LazyCollection;

final class BlogDataRetriever
{
    /**
     * Get old blog post categories.
     *
     * @return LazyCollection
     */
    public function getOldBlogPostCategories(): LazyCollection
    {
        return DB::connection('mysql_old')
            ->table('blog_post_categories')
            ->whereNull('deleted_at')
            ->lazyById(100);
    }

    /**
     * Get old blog post category by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getOldBlogPostCategoryById(int $id): mixed
    {
        return DB::connection('mysql_old')
            ->table('blog_post_categories')
            ->whereNull('deleted_at')
            ->find($id);
    }

    /**
     * Get old blog posts.
     *
     * @return LazyCollection
     */
    public function getOldBlogPosts(): LazyCollection
    {
        return DB::connection('mysql_old')
            ->table('blog_posts')
            ->whereNull('deleted_at')
            ->lazyById(100);
    }
}
