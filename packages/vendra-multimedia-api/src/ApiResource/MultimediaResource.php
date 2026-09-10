<?php

declare(strict_types=1);

namespace Misaf\VendraMultimediaApi\ApiResource;

use ApiPlatform\Laravel\Eloquent\Filter\EqualsFilter;
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
use Misaf\VendraMultimediaApi\State\MultimediaLinksHandler;
use Misaf\VendraMultimediaApi\State\MultimediaMapper;
use Misaf\VendraMultimediaApi\State\PublicMultimedia;

#[ApiResource(
    shortName: 'Multimedia',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: PublicMultimedia::class,
        handleLinks: MultimediaLinksHandler::class,
        mapper: MultimediaMapper::class,
    ),
    mcp: [
        'get_multimedia' => new McpTool(
            description: 'Get a public multimedia asset and its conversions by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_multimedia' => new McpToolCollection(
            description: 'List public multimedia assets and their conversions.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/content/multimedia/{id}')]
#[GetCollection(
    uriTemplate: '/content/multimedia',
    parameters: [
        'mimeType' => new QueryParameter(key: 'mimeType', property: 'mime_type', filter: EqualsFilter::class, constraints: ['string', 'max:255']),
    ],
)]
final readonly class MultimediaResource
{
    /**
     * @param  array<string, mixed>  $generatedConversions
     * @param  array<string, mixed>  $customProperties
     * @param  array<string, mixed>  $responsiveImages
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The multimedia asset unique identifier')]
        public int $id,
        public string $uuid,
        public string $name,
        public string $fileName,
        public string $collection,
        public ?string $mimeType,
        public int $bytes,
        public string $disk,
        public ?string $url,
        public array $generatedConversions,
        public array $customProperties,
        public array $responsiveImages,
    ) {}
}
