<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPageApi\ApiResource;

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
use Misaf\VendraCustomPage\Models\CustomPageCategory;
use Misaf\VendraCustomPageApi\State\CustomPageCategoryLinksHandler;
use Misaf\VendraCustomPageApi\State\CustomPageCategoryMapper;

#[ApiResource(
    shortName: 'CustomPageCategory',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: CustomPageCategory::class,
        handleLinks: CustomPageCategoryLinksHandler::class,
        mapper: CustomPageCategoryMapper::class,
    ),
    mcp: [
        'get_custom_page_category' => new McpTool(
            description: 'Get a custom-page category by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_custom_page_categories' => new McpToolCollection(
            description: 'List custom-page categories.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/content/custom-page-categories/{id}')]
#[GetCollection(uriTemplate: '/content/custom-page-categories')]
final readonly class CustomPageCategoryResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<int, ResourceReference>  $customPages
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The custom page category unique identifier')]
        public int $id,
        public array $name,
        public array $customPages,
    ) {}
}
