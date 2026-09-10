<?php

declare(strict_types=1);

namespace Misaf\VendraBlogApi\ApiResource;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\McpToolCollection;
use Misaf\VendraApi\ApiResource\McpCollectionInput;
use Misaf\VendraApi\ApiResource\McpResourceIdentifierInput;
use Misaf\VendraApi\ApiResource\ResourceReference;
use Misaf\VendraApi\State\EloquentResourceOptions;
use Misaf\VendraApi\State\EloquentResourceProvider;
use Misaf\VendraBlog\Models\BlogPostCategory;
use Misaf\VendraBlogApi\State\BlogPostCategoryLinksHandler;
use Misaf\VendraBlogApi\State\BlogPostCategoryMapper;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;

#[ApiResource(
    shortName: 'BlogPostCategory',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: BlogPostCategory::class,
        handleLinks: BlogPostCategoryLinksHandler::class,
        mapper: BlogPostCategoryMapper::class,
    ),
    mcp: [
        'get_blog_post_category' => new McpTool(
            description: 'Get an active blog-post category by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_blog_post_categories' => new McpToolCollection(
            description: 'List active blog-post categories.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/content/blog-post-categories/{id}')]
#[GetCollection(uriTemplate: '/content/blog-post-categories')]
final readonly class BlogPostCategoryResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, string>  $slug
     * @param  array<string, array<array-key, mixed>|string>  $description
     * @param  array<int, ResourceReference>  $blogPosts
     * @param  array<int, MultimediaResource>  $multimedia
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The blog post category unique identifier')]
        public int $id,
        public array $name,
        public array $slug,
        public array $description,
        public int $position,
        public bool $active,
        public array $blogPosts,
        public array $multimedia,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
