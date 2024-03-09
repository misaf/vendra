<?php

declare(strict_types=1);

namespace App\Console\Commands\ConvertData\Converter;

use App\Console\Commands\ConvertData\Interfaces\DataConverter;
use App\Console\Commands\ConvertData\Retriever\BlogDataRetriever;
use App\Models\Blog\BlogPost;
use App\Models\Blog\BlogPostCategory;
use Illuminate\Contracts\Filesystem\Filesystem as Storage;
use Illuminate\Support\Collection;

final class BlogDataConverter implements DataConverter
{
    public function __construct(public Storage $storage, public BlogDataRetriever $dataRetriever) {}

    public function migrate(): void
    {
        $this->migrateBlogPostCategories();
        $this->migrateBlogPostCategoryImages();
        $this->migrateBlogPosts();
        $this->migrateBlogPostImages();
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
        $this->dataRetriever->getOldBlogPostCategories()
            ->each(fn($oldBlogPostCategory): BlogPostCategory => $this->createNewBlogPostCategory($oldBlogPostCategory));
    }

    private function migrateBlogPostCategoryImages(): void
    {
        $this->dataRetriever->getOldBlogPostCategories()
            ->each(function ($oldBlogPostCategoryImage): void {
                if ($this->shouldSkipMigration($oldBlogPostCategoryImage->image)) {
                    return;
                }

                BlogPostCategory::where('slug', $oldBlogPostCategoryImage->slug)->first()
                    ->addMedia(storage_path('app/old-images/' . $oldBlogPostCategoryImage->image))
                    ->preservingOriginal()
                    ->withResponsiveImages()
                    ->toMediaCollection();
            });
    }

    private function migrateBlogPostImages(): void
    {
        $this->dataRetriever->getOldBlogPosts()
            ->each(function ($oldBlogPostImage): void {
                if ($this->shouldSkipMigration($oldBlogPostImage->image)) {
                    return;
                }

                BlogPost::where('slug', $oldBlogPostImage->slug)->first()
                    ->addMedia(storage_path('app/old-images/' . $oldBlogPostImage->image))
                    ->preservingOriginal()
                    ->withResponsiveImages()
                    ->toMediaCollection();
            });
    }

    private function migrateBlogPosts(): void
    {
        $this->dataRetriever->getOldBlogPosts()
            ->each(fn($oldBlogPost): BlogPost => $this->createNewBlogPost($oldBlogPost));
    }

    private function resync(): void
    {
        BlogPostCategory::chunkById(100, function (Collection $blogPostCategories): void {
            foreach ($blogPostCategories as $blogPostCategory) {
                $blogPostCategory->delete();
            }
        });

        BlogPost::chunkById(100, function (Collection $blogPosts): void {
            foreach ($blogPosts as $blogPost) {
                $blogPost->delete();
            }
        });

        // Migrate data again from the old system
        $this->migrate();
    }

    private function shouldSkipMigration(?string $image): bool
    {
        return (null === $image || ! $this->storage->exists('old-images/' . $image));
    }
}
