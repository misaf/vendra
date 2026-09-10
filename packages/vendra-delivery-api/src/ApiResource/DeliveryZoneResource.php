<?php

declare(strict_types=1);

namespace Misaf\VendraDeliveryApi\ApiResource;

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
use Misaf\VendraApi\State\EloquentResourceOptions;
use Misaf\VendraApi\State\EloquentResourceProvider;
use Misaf\VendraDelivery\Models\DeliveryZone;
use Misaf\VendraDeliveryApi\State\DeliveryZoneLinksHandler;
use Misaf\VendraDeliveryApi\State\DeliveryZoneMapper;

#[ApiResource(
    shortName: 'DeliveryZone',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: DeliveryZone::class,
        handleLinks: DeliveryZoneLinksHandler::class,
        mapper: DeliveryZoneMapper::class,
    ),
    mcp: [
        'get_delivery_zone' => new McpTool(
            description: 'Get one active delivery zone by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_delivery_zones' => new McpToolCollection(
            description: 'List the active delivery zones, tightest band first.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/delivery/zones/{id}')]
#[GetCollection(
    uriTemplate: '/delivery/zones',
    order: ['position' => 'ASC'],
    parameters: [
        'sort[position]' => new QueryParameter(key: 'sort[position]', property: 'position', filter: OrderFilter::class),
    ],
)]
final readonly class DeliveryZoneResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, string>|null  $description
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The delivery zone unique identifier')]
        public int $id,
        public array $name,
        public ?array $description,
        public ?float $maxDistanceKm,
        public string $currencyCode,
        public int $feeAmount,
        public bool $requiresQuote,
        public int $position,
    ) {}
}
