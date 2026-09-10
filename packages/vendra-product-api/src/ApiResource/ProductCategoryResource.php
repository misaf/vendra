<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\ApiResource;

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
use Misaf\VendraApi\State\EloquentResourceOptions;
use Misaf\VendraApi\State\EloquentResourceProvider;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;
use Misaf\VendraProduct\Models\ProductCategory;
use Misaf\VendraProductApi\State\ProductCategoryLinksHandler;
use Misaf\VendraProductApi\State\ProductCategoryMapper;

#[ApiResource(
    shortName: 'ProductCategory',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: ProductCategory::class,
        handleLinks: ProductCategoryLinksHandler::class,
        mapper: ProductCategoryMapper::class,
    ),
    mcp: [
        'get_product_category' => new McpTool(
            description: 'Get an active product category by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_product_categories' => new McpToolCollection(
            description: 'List active product categories with their titles, slugs, and the products in each.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/catalog/product-categories/{id}')]
#[GetCollection(
    uriTemplate: '/catalog/product-categories',
    order: ['position' => 'ASC'],
    parameters: [
        'sort[id]' => new QueryParameter(key: 'sort[id]', property: 'id', filter: OrderFilter::class),
        'sort[position]' => new QueryParameter(key: 'sort[position]', property: 'position', filter: OrderFilter::class),
    ],
)]
final readonly class ProductCategoryResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, string>  $slug
     * @param  array<string, array<array-key, mixed>|string>  $description
     * @param  array<int, ResourceReference>  $products
     * @param  array<int, MultimediaResource>  $multimedia
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The product category unique identifier')]
        public int $id,
        public array $name,
        public array $slug,
        public array $description,
        public int $position,
        public bool $active,
        public array $products,
        public array $multimedia,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
