<?php

declare(strict_types=1);

namespace Misaf\VendraCustomPageApi\ApiResource;

use ApiPlatform\Laravel\Eloquent\Filter\BooleanFilter;
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
use Misaf\VendraCustomPage\Models\CustomPage;
use Misaf\VendraCustomPageApi\State\CustomPageLinksHandler;
use Misaf\VendraCustomPageApi\State\CustomPageMapper;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;

#[ApiResource(
    shortName: 'CustomPage',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: CustomPage::class,
        handleLinks: CustomPageLinksHandler::class,
        mapper: CustomPageMapper::class,
    ),
    mcp: [
        'get_custom_page' => new McpTool(
            description: 'Get an active custom content page by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_custom_pages' => new McpToolCollection(
            description: 'List active custom content pages.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/content/custom-pages/{id}')]
#[GetCollection(
    uriTemplate: '/content/custom-pages',
    parameters: [
        'active' => new QueryParameter(key: 'active', property: 'active', filter: BooleanFilter::class, constraints: ['boolean']),
    ],
)]
final readonly class CustomPageResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, array<array-key, mixed>|string>  $description
     * @param  array<string, string>  $slug
     * @param  array<int, MultimediaResource>  $multimedia
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The custom page unique identifier')]
        public int $id,
        public array $name,
        public array $description,
        public array $slug,
        public int $position,
        public bool $active,
        public ResourceReference $customPageCategory,
        public array $multimedia,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
