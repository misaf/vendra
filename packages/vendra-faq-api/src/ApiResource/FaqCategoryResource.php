<?php

declare(strict_types=1);

namespace Misaf\VendraFaqApi\ApiResource;

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
use Misaf\VendraFaq\Models\FaqCategory;
use Misaf\VendraFaqApi\State\FaqCategoryLinksHandler;
use Misaf\VendraFaqApi\State\FaqCategoryMapper;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;

#[ApiResource(
    shortName: 'FaqCategory',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: FaqCategory::class,
        handleLinks: FaqCategoryLinksHandler::class,
        mapper: FaqCategoryMapper::class,
    ),
    mcp: [
        'get_faq_category' => new McpTool(
            description: 'Get an active FAQ category by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_faq_categories' => new McpToolCollection(
            description: 'List active FAQ categories.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/content/faq-categories/{id}')]
#[GetCollection(uriTemplate: '/content/faq-categories')]
final readonly class FaqCategoryResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, string>  $slug
     * @param  array<string, array<array-key, mixed>|string>  $description
     * @param  array<int, ResourceReference>  $faqs
     * @param  array<int, MultimediaResource>  $multimedia
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The FAQ category unique identifier')]
        public int $id,
        public array $name,
        public array $slug,
        public array $description,
        public int $position,
        public bool $active,
        public array $faqs,
        public array $multimedia,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
