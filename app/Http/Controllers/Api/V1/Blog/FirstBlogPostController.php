<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Blog;

use App\Http\Controllers\Controller;
use App\Http\Resources\Blog\BlogPostResource;
use App\Models\Blog\BlogPost;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

final class FirstBlogPostController extends Controller
{
    public function __invoke()
    {
        $query = QueryBuilder::for(BlogPost::class)
            ->allowedIncludes([
                AllowedInclude::relationship('blog_post_category', 'blogPostCategory'),
                'media'
            ])
            ->allowedFilters(['name', 'slug', 'status'])
            ->allowedSorts('position')
            ->defaultSort('-position')
            ->first();

        return new BlogPostResource($query);
    }
}
