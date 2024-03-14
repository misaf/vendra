<?php

declare(strict_types=1);

namespace App\Console\Commands\ConvertData\Converter;

use App\Console\Commands\ConvertData\Interfaces\DataConverter;
use App\Console\Commands\ConvertData\Retriever\BlogDataRetriever;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogPostCategory;
use Illuminate\Contracts\Filesystem\Filesystem as Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\MediaLibrary\HasMedia;

final class BlogDataConverter implements DataConverter
{
    public function __construct(public Storage $storage, public BlogDataRetriever $dataRetriever) {}

    public function migrate(): void
    {
        $this->resync();

        DB::transaction(function (): void {
            $this->migrateBlogPostCategories();
            $this->migrateBlogPostCategoryImages();
            $this->migrateBlogPosts();
            $this->migrateBlogPostImages();
        });
    }

    private function clearBlogPostCategoriesMediaCollections(): void
    {
        BlogPostCategory::chunkById(100, function (Collection $blogPostCategories): void {
            $blogPostCategories->each(fn(BlogPostCategory $blogPostCategory): HasMedia => $blogPostCategory->clearMediaCollection());
        });
    }

    private function clearBlogPostsMediaCollections(): void
    {
        BlogPost::chunkById(100, function (Collection $blogPosts): void {
            $blogPosts->each(fn(BlogPost $blogPost): HasMedia => $blogPost->clearMediaCollection());
        });
    }

    private function createNewBlogPost(mixed $oldBlogPost): BlogPost
    {
        $getOldBlogPostCategory = $this->dataRetriever->getOldBlogPostCategoryById($oldBlogPost->blog_post_category_id);

        $blogPostCategoryId = BlogPostCategory::where('slug', $getOldBlogPostCategory->slug)->value('id');

        $newBlogPost = new BlogPost();

        $newBlogPost->setRawAttributes([
            'blog_post_category_id' => $blogPostCategoryId,
            'name'                  => $oldBlogPost->name,
            'description'           => $oldBlogPost->description,
            'slug'                  => $oldBlogPost->slug,
            'status'                => 1,
        ]);

        $newBlogPost->save();

        return $newBlogPost;
    }

    private function createNewBlogPostCategory(mixed $oldBlogPostCategory): BlogPostCategory
    {
        $newBlogPostCategory = new BlogPostCategory();

        $newBlogPostCategory->setRawAttributes([
            'name'   => $oldBlogPostCategory->name,
            'slug'   => $oldBlogPostCategory->slug,
            'status' => 1
        ]);

        $newBlogPostCategory->save();

        return $newBlogPostCategory;
    }

    private function migrateBlogPostCategories(): void
    {
        $getOldBlogPostCategories = $this->dataRetriever->getOldBlogPostCategories();

        $getOldBlogPostCategories->each(fn($oldBlogPostCategory): BlogPostCategory => $this->createNewBlogPostCategory($oldBlogPostCategory));
    }

    private function migrateBlogPostCategoryImages(): void
    {
        $getOldBlogPostCategories = $this->dataRetriever->getOldBlogPostCategories();

        $getOldBlogPostCategories
            ->reject(fn($oldBlogPostCategory): bool => $this->shouldSkipMigration($oldBlogPostCategory->image))
            ->each(function ($oldBlogPostCategory): void {
                BlogPostCategory::where('slug', $oldBlogPostCategory->slug)
                    ->first()
                    ->addMedia(storage_path('app/public/old-images/' . $oldBlogPostCategory->image))
                    ->preservingOriginal()
                    ->toMediaCollection();
            });
    }

    private function migrateBlogPostImages(): void
    {
        $getOldBlogPosts = $this->dataRetriever->getOldBlogPosts();

        $getOldBlogPosts
            ->reject(fn($oldBlogPost): bool => $this->shouldSkipMigration($oldBlogPost->image))
            ->each(function ($oldBlogPost): void {
                BlogPost::where('slug', $oldBlogPost->slug)
                    ->first()
                    ->addMedia(storage_path('app/public/old-images/' . $oldBlogPost->image))
                    ->preservingOriginal()
                    ->toMediaCollection();
            });
    }

    private function migrateBlogPosts(): void
    {
        $getOldBlogPosts = $this->dataRetriever->getOldBlogPosts();

        $getOldBlogPosts->each(fn($oldBlogPost): BlogPost => $this->createNewBlogPost($oldBlogPost));
    }

    private function resync(): void
    {
        $this->clearBlogPostsMediaCollections();
        $this->clearBlogPostCategoriesMediaCollections();
        $this->truncateTables();
    }

    private function shouldSkipMigration(?string $image): bool
    {
        return (null === $image || ! $this->storage->exists('old-images/' . $image));
    }

    private function truncateTables(): void
    {
        Schema::disableForeignKeyConstraints();
        BlogPost::truncate();
        BlogPostCategory::truncate();
        Schema::enableForeignKeyConstraints();
    }
}
