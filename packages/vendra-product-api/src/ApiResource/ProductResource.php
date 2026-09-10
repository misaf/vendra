<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\ApiResource;

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
use ApiPlatform\OpenApi\Model\Operation as OpenApiOperation;
use Misaf\VendraApi\ApiResource\McpCollectionInput;
use Misaf\VendraApi\ApiResource\McpResourceIdentifierInput;
use Misaf\VendraApi\ApiResource\ResourceReference;
use Misaf\VendraApi\Eloquent\Filter\LocalizedEqualsFilter;
use Misaf\VendraApi\Eloquent\Filter\LocalizedSearchFilter;
use Misaf\VendraApi\Eloquent\Filter\RandomOrderFilter;
use Misaf\VendraApi\State\EloquentResourceOptions;
use Misaf\VendraApi\State\EloquentResourceProvider;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;
use Misaf\VendraProduct\Models\Product;
use Misaf\VendraProductApi\State\ProductLinksHandler;
use Misaf\VendraProductApi\State\ProductMapper;

#[ApiResource(
    shortName: 'Product',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: Product::class,
        handleLinks: ProductLinksHandler::class,
        mapper: ProductMapper::class,
    ),
    mcp: [
        'get_product' => new McpTool(
            description: 'Get an active catalog product with its category, prices, media, and attribute options by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_products' => new McpToolCollection(
            description: 'List active catalog products with their categories, prices, media, and attribute options.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(
    uriTemplate: '/catalog/products/{id}',
    openapi: new OpenApiOperation(tags: ['Product']),
)]
#[GetCollection(
    uriTemplate: '/catalog/products',
    order: ['position' => 'ASC'],
    openapi: new OpenApiOperation(tags: ['Product']),
    parameters: [
        'inStock' => new QueryParameter(key: 'inStock', property: 'in_stock', filter: BooleanFilter::class, constraints: ['boolean']),
        'categoryId' => new QueryParameter(key: 'categoryId', property: 'product_category_id', filter: EqualsFilter::class, constraints: ['integer', 'min:1']),
        'token' => new QueryParameter(key: 'token', property: 'token', filter: EqualsFilter::class, constraints: ['string', 'max:255']),
        'slug' => new QueryParameter(key: 'slug', property: 'slug', filter: LocalizedEqualsFilter::class, constraints: ['string', 'max:255']),
        'search' => new QueryParameter(
            key: 'search',
            filter: LocalizedSearchFilter::class,
            filterContext: ['properties' => ['name' => true, 'slug' => true, 'token' => false]],
            constraints: ['string', 'max:255'],
        ),
        'sort[id]' => new QueryParameter(key: 'sort[id]', property: 'id', filter: OrderFilter::class),
        'sort[position]' => new QueryParameter(key: 'sort[position]', property: 'position', filter: OrderFilter::class),
        'sort[createdAt]' => new QueryParameter(key: 'sort[createdAt]', property: 'created_at', filter: OrderFilter::class),
        'random' => new QueryParameter(
            key: 'random',
            filter: RandomOrderFilter::class,
            constraints: ['boolean'],
            castToNativeType: true,
        ),
    ],
)]
final readonly class ProductResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, string>  $slug
     * @param  array<string, array<array-key, mixed>|string>  $description
     * @param  array<int, ProductPriceResource>  $productPrices
     * @param  array<int, MultimediaResource>  $multimedia
     * @param  array<int, ResourceReference>  $options
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The product unique identifier')]
        public int $id,
        public array $name,
        public array $slug,
        public array $description,
        public string $token,
        public int $quantity,
        public bool $inStock,
        public ?int $stockThreshold,
        public ?int $position,
        public ?bool $availableSoon,
        public ?string $availabilityDate,
        public ResourceReference $productCategory,
        public array $productPrices,
        public ?ProductPriceResource $latestProductPrice,
        public array $multimedia,
        public array $options,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
