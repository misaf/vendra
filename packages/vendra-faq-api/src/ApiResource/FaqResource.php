<?php

declare(strict_types=1);

namespace Misaf\VendraFaqApi\ApiResource;

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
use Misaf\VendraFaq\Models\Faq;
use Misaf\VendraFaqApi\State\FaqLinksHandler;
use Misaf\VendraFaqApi\State\FaqMapper;
use Misaf\VendraMultimediaApi\ApiResource\MultimediaResource;

#[ApiResource(
    shortName: 'Faq',
    provider: EloquentResourceProvider::class,
    stateOptions: new EloquentResourceOptions(
        modelClass: Faq::class,
        handleLinks: FaqLinksHandler::class,
        mapper: FaqMapper::class,
    ),
    mcp: [
        'get_faq' => new McpTool(
            description: 'Get an active FAQ with its category and media by identifier.',
            input: McpResourceIdentifierInput::class,
            provider: EloquentResourceProvider::class,
        ),
        'list_faqs' => new McpToolCollection(
            description: 'List active FAQs with their categories and media.',
            input: McpCollectionInput::class,
            provider: EloquentResourceProvider::class,
        ),
    ],
)]
#[Get(uriTemplate: '/content/faqs/{id}')]
#[GetCollection(
    uriTemplate: '/content/faqs',
    order: ['position' => 'ASC'],
    parameters: [
        'categoryId' => new QueryParameter(key: 'categoryId', property: 'faq_category_id', filter: EqualsFilter::class, constraints: ['integer', 'min:1']),
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
final readonly class FaqResource
{
    /**
     * @param  array<string, string>  $name
     * @param  array<string, array<array-key, mixed>|string>  $description
     * @param  array<string, string>  $slug
     * @param  array<int, MultimediaResource>  $multimedia
     */
    public function __construct(
        #[ApiProperty(identifier: true, description: 'The FAQ unique identifier')]
        public int $id,
        public array $name,
        public array $description,
        public array $slug,
        public int $position,
        public bool $active,
        public ResourceReference $faqCategory,
        public array $multimedia,
        public string $createdAt,
        public string $updatedAt,
    ) {}
}
