<?php

declare(strict_types=1);

namespace Misaf\VendraAttributeApi\ApiResource;

use ApiPlatform\Laravel\Eloquent\Filter\BooleanFilter;
use ApiPlatform\Laravel\Eloquent\Filter\PartialSearchFilter;
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
use Misaf\VendraAttribute\Models\Attribute;
use Misaf\VendraAttributeApi\State\AttributeLinksHandler;
use Misaf\VendraAttributeApi\State\AttributeMapper;

#[ApiResource(
    shortName: 'Attribute',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: Attribute::class,
        handleLinks: AttributeLinksHandler::class,
        mapper: AttributeMapper::class,
    ),
    mcp: [
        'get_attribute' => new McpTool(
            description: 'Get an active catalog attribute and its values by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_attributes' => new McpToolCollection(
            description: 'List active catalog attributes and their values.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/catalog/attributes/{id}')]
#[GetCollection(
    uriTemplate: '/catalog/attributes',
    parameters: [
        'search' => new QueryParameter(key: 'search', property: 'name', filter: PartialSearchFilter::class, constraints: ['string', 'min:2', 'max:100']),
        'active' => new QueryParameter(key: 'active', property: 'active', filter: BooleanFilter::class, constraints: ['boolean']),
    ],
)]
final readonly class AttributeResource
{
    /**
     * @param  array<int, ResourceReference>  $values
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The attribute unique identifier')]
        public int $id,
        public string $name,
        public ?string $description,
        public ?string $unit,
        public int $position,
        public bool $active,
        public array $values,
    ) {}
}
