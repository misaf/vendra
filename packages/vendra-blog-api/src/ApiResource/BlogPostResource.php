<?php

declare(strict_types=1);

namespace Misaf\VendraBlogApi\ApiResource;

use ApiPlatform\Laravel\Eloquent\Filter\BooleanFilter;
use ApiPlatform\Laravel\Eloquent\Filter\EqualsFilter;
use ApiPlatform\Laravel\Eloquent\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\McpTool;
use ApiPlatform\Metadata\McpToolCollection;
use ApiPlatform\Metadata\QueryParameter;
use Misaf\VendraApi\ApiResource\McpCollectionInput;
use Misaf\VendraApi\ApiResource\McpResourceIdentifierInput;
use Misaf\VendraApi\ApiResource\ResourceReference;
use Misaf\VendraApi\Eloquent\Filter\LocalizedEqualsFilter;
use Misaf\VendraApi\Eloquent\Filter\LocalizedSearchFilter;
use Misaf\VendraApi\State\EloquentResourceOptions;
use Misaf\VendraApi\State\EloquentResourceProvider;
use Misaf\VendraBlog\Models\BlogPost;
use Misaf\VendraBlogApi\State\BlogPostLinksHandler;
use Misaf\VendraBlogApi\State\BlogPostMapper;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;

#[ApiResource(
    shortName: 'BlogPost',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: BlogPost::class,
        handleLinks: BlogPostLinksHandler::class,
        mapper: BlogPostMapper::class,
    ),
    mcp: [
        'get_blog_post' => new McpTool(
            description: 'Get an active blog post with its category and media by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_blog_posts' => new McpToolCollection(
            description: 'List active blog posts with their categories and media.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/content/blog-posts/{id}')]
#[GetCollection(
    uriTemplate: '/content/blog-posts',
    order: ['created_at' => 'DESC'],
    parameters: [
        'active' => new QueryParameter(key: 'active', property: 'active', filter: BooleanFilter::class, constraints: ['boolean']),
        'categoryId' => new QueryParameter(key: 'categoryId', property: 'blog_post_category_id', filter: EqualsFilter::class, constraints: ['integer', 'min:1']),
        'slug' => new QueryParameter(key: 'slug', property: 'slug', filter: LocalizedEqualsFilter::class, constraints: ['string', 'max:255']),
        'search' => new QueryParameter(
            key: 'search',
            filter: LocalizedSearchFilter::class,
            filterContext: ['properties' => ['name' => true, 'slug' => true]],
            constraints: ['string', 'max:255'],
        ),
        'sort[position]' => new QueryParameter(key: 'sort[position]', property: 'position', filter: OrderFilter::class),
        'sort[createdAt]' => new QueryParameter(key: 'sort[createdAt]', property: 'created_at', filter: OrderFilter::class),
    ],
)]
final readonly class BlogPostResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, array<array-key, mixed>|string>  $description
     * @param  array<string, string>  $slug
     * @param  array<int, MultimediaResource>  $multimedia
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The blog post unique identifier')]
        public int $id,
        public array $name,
        public array $description,
        public array $slug,
        public bool $active,
        public int $position,
        public ResourceReference $blogPostCategory,
        public array $multimedia,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
