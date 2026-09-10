<?php

declare(strict_types=1);

namespace Misaf\VendraProductApi\ApiResource;

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
use Misaf\VendraApi\State\EloquentResourceOptions;
use Misaf\VendraApi\State\EloquentResourceProvider;
use Misaf\VendraProduct\Models\ProductPrice;
use Misaf\VendraProductApi\State\ProductPriceLinksHandler;
use Misaf\VendraProductApi\State\ProductPriceMapper;

#[ApiResource(
    shortName: 'ProductPrice',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: ProductPrice::class,
        handleLinks: ProductPriceLinksHandler::class,
        mapper: ProductPriceMapper::class,
    ),
    mcp: [
        'get_product_price' => new McpTool(
            description: 'Get a product price by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_product_prices' => new McpToolCollection(
            description: 'List product prices with their minor-unit amount, currency, formatted value, and linked product.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/catalog/product-prices/{id}')]
#[GetCollection(
    uriTemplate: '/catalog/product-prices',
    parameters: [
        'currency' => new QueryParameter(key: 'currency', property: 'currency_code', filter: EqualsFilter::class, constraints: ['string', 'size:3']),
        'productId' => new QueryParameter(key: 'productId', property: 'product_id', filter: EqualsFilter::class, constraints: ['integer', 'min:1']),
        'sort[id]' => new QueryParameter(key: 'sort[id]', property: 'id', filter: OrderFilter::class),
        'sort[price]' => new QueryParameter(key: 'sort[price]', property: 'price', filter: OrderFilter::class),
    ],
)]
final readonly class ProductPriceResource
{
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The product price unique identifier')]
        public int $id,
        public int $minorAmount,
        public float|int $amount,
        public string $currency,
        public string $formatted,
        public ResourceReference $product,
    ) {}
}
